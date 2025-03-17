/**
 * https://github.com/centrifugal/centrifuge-js?tab=readme-ov-file#install
 */
import {Centrifuge} from "centrifuge";

document.addEventListener("moonshine:init", () => {

    const centrifuge = new Centrifuge("ws://localhost:8000/connection/websocket", {
        token: '<YOUR_TOKEN>'
    });

    centrifuge.on('connected', () => {
        document.dispatchEvent(new CustomEvent('moonshine:rush'))
    }).connect()

    MoonShine.onCallback('onMoonShineWS', function(channel, onRush) {
        const sub = centrifuge.newSubscription(channel);
        sub.on('publication', function (ctx) {
            onRush(ctx.data)
        }).on('subscribing', () => {
            document.dispatchEvent(new CustomEvent('rush-subscribe:'))
        }).subscribe();
    })

    MoonShine.onCallback('rushPublish', function(channel, data) {
        centrifuge.publish(channel, data).then();
    })
})