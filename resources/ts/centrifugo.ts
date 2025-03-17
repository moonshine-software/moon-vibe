import { Centrifuge, PublicationContext } from "centrifuge";
import * as jose from "jose";

declare global {
    interface Window {
        MoonShine: {
            onCallback: (name: string, callback: Function) => void;
        }
    }
    
    interface ImportMeta {
        env: {
            VITE_CENTRIFUGO_SECRET: string;
            [key: string]: string;
        }
    }
}

document.addEventListener("moonshine:init", async () => {
    if (! window.MoonShine) {
        console.error('MoonShine is not initialized');
        return;
    }
    
    let token: string|null = localStorage.getItem('token')
    if(token === null) {
        token = await getToken()
        localStorage.setItem('token', token)
    }

    console.log('centrifuge', token)

    const centrifuge = new Centrifuge("ws://localhost:8000/connection/websocket", {
        token: token
    });

    centrifuge.on('connected', () => {
        document.dispatchEvent(new CustomEvent('moonshine:rush'));
    }).connect();

    window.MoonShine.onCallback('onMoonShineWS', function(channel: string, onRush: (data: any) => void): void {
        const sub = centrifuge.newSubscription(channel);
        sub.on('publication', function(ctx: PublicationContext): void {
            onRush(ctx.data);
        }).on('subscribing', (): void => {
            document.dispatchEvent(new CustomEvent('rush-subscribe:'));
        }).subscribe();
    });

    window.MoonShine.onCallback('rushPublish', function(channel: string, data: any[]): void {
        centrifuge.publish(channel, data).then().catch(error => {
            console.error('Failed to publish message:', error);
        });
    });
});

async function getToken(): Promise<string> {
    const secret = new TextEncoder().encode(import.meta.env.VITE_CENTRIFUGO_SECRET)
    const alg = 'HS256'
    return await new jose.SignJWT({ sub: '42' })
        .setProtectedHeader({ alg })
        .sign(secret)
}