import { Centrifuge, PublicationContext } from "centrifuge";
import axios from "axios";

declare global {
    interface Window {
        MoonShine: {
            onCallback: (name: string, callback: Function) => void;
        }
    }

    interface ImportMeta {
        env: {
            [key: string]: string;
        }
    }
}

document.addEventListener("moonshine:init", async () => {
    if (! window.MoonShine) {
        console.error('MoonShine is not initialized');
        return;
    }

    let token = await getOrCreateToken();

    const wsUrl = getWsURL()
    const centrifuge = new Centrifuge(wsUrl, {
        token: token
    });

    centrifuge.on('connected', () => {
        document.dispatchEvent(new CustomEvent('moonshine:rush'));
    }).connect();

    window.MoonShine.onCallback('onMoonShineWS', function(channel: string, onRush: (data: any) => void): void {
        if(centrifuge.getSubscription(channel) !== null) {
            return;
        }

        const sub = centrifuge.newSubscription(channel);

        sub.on('publication', function(ctx: PublicationContext): void {
            onRush(ctx.data);
        }).on('subscribing', (): void => {
            document.dispatchEvent(new CustomEvent('rush-subscribe:'));
        }).on('error', (error): void => {
            console.log(error)
        })
            .subscribe()
    });

    window.MoonShine.onCallback('rushPublish', function(channel: string, data: any[]): void {
        centrifuge.publish(channel, data).then().catch(error => {
            console.error('Failed to publish message:', error);
        });
    });
});

async function getOrCreateToken(): Promise<string> {
    const storedToken = localStorage.getItem('centrifugo_token');
    const storedExpiration = localStorage.getItem('centrifugo_token_expiration');

    const now = Math.floor(Date.now() / 1000);

    if (storedToken && storedExpiration && parseInt(storedExpiration) > (now + 300)) {
        return storedToken;
    }

    try {
        const response = await axios.post('/centrifugo/token');
        const token = response.data.token;

        const [, payload] = token.split('.');
        const decodedPayload = JSON.parse(atob(payload));
        const expiration = decodedPayload.exp || 0;

        localStorage.setItem('centrifugo_token', token);
        localStorage.setItem('centrifugo_token_expiration', expiration.toString());

        return token;
    } catch (error) {
        console.error('Failed to get Centrifugo token:', error);
        throw error;
    }
}

function getWsURL(): string {
    const { hostname } = window.location;

    if(hostname === 'admin-builder.getmoonshine.app') {
        return 'wss://wss.admin-builder.getmoonshine.app/connection/websocket'
    }

    return import.meta.env.VITE_CENTRIFUGO_WS_URL;
}

