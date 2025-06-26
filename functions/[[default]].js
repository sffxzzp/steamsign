const defaultSteamID = "76561198137595648";

let arrayBufferToBase64 = function (buffer) {
    let binary = '';
    let bytes = new Uint8Array(buffer);
    let len = bytes.byteLength;
    for (let i = 0; i < len; i++) {
        binary += String.fromCharCode(bytes[i]);
    }
    return btoa(binary);
}

let getWebImage = async function (url) {
    return 'data:image/jpeg;base64,' + arrayBufferToBase64(await fetch(url).then(res => res.arrayBuffer()))
}

let getHeaderImage = async function (appid) {
    let data = await fetch(`https://store.steampowered.com/api/appdetails?appids=${appid}`).then(res => res.json());
    let headerImage = data[appid]['data']['header_image'];
    if (headerImage != '') {
        return headerImage;
    } else {
        return `https://steamcdn-a.akamaihd.net/steam/apps/${appid}/header.jpg`;
    }
}

let getUserInfo = async function (steamid, recentNum, apikey) {
    let userInfo = {}, data;
    let userUrl = `https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=${apikey}&steamids=${steamid}`;
    data = await fetch(userUrl).then(res => res.json());
    userInfo['username'] = data.response.players[0].personaname || "";
    userInfo['avatar'] = data.response.players[0].avatarfull || "";
    let lvlUrl = `https://api.steampowered.com/IPlayerService/GetBadges/v0001/?key=${apikey}&steamid=${steamid}&format=json`;
    data = await fetch(lvlUrl).then(res => res.json());
    userInfo['level'] = data.response.player_level || 0;
    userInfo['gameNum'] = 0;
    for (let i = 0; i < data.response.badges.length; i++) {
        if (data.response.badges[i].badgeid == 13) {
            userInfo['gameNum'] = data.response.badges[i].level || 0;
            break;
        }
    }
    let recentUrl = `https://api.steampowered.com/IPlayerService/GetRecentlyPlayedGames/v0001/?key=${apikey}&steamid=${steamid}&format=json`;
    data = await fetch(recentUrl).then(res => res.json());
    userInfo['recent'] = [];
    if (recentNum > data.response.total_count) {
        recentNum = data.response.total_count || 3;
    }
    for (let i = 0; i < recentNum; i++) {
        userInfo['recent'].push(await getHeaderImage(data['response']['games'][i]['appid']));
    }
    return userInfo;
}

let renderCard = async function (userInfo) {
    let imgTpl = "";
    for (let i = 0; i < userInfo.recent.length; i++) {
        imgTpl += `<image height="75" width="155" x="${i * 160 + 150}" y="65" xlink:href="${await getWebImage(userInfo.recent[i])}"></image>`
    }
    const svgdata = `<svg width="635" height="150" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg"><style>.simsun {font-family: Consolas, "Nimbus Roman No9 L", "Songti SC", "Noto Serif CJK SC", "Source Han Serif SC", "Source Han Serif CN", STSong, "AR PL New Sung", "AR PL SungtiL GB", NSimSun, SimSun, "TW\-Sung", "WenQuanYi Bitmap Song", "AR PL UMing CN", "AR PL UMing HK", "AR PL UMing TW", "AR PL UMing TW MBE", PMingLiU, MingLiU, serif;}.simkai {font-family: Baskerville, Consolas, "Liberation Serif", "Kaiti SC", STKaiti, "AR PL UKai CN", "AR PL UKai HK", "AR PL UKai TW", "AR PL UKai TW MBE", "AR PL KaitiM GB", KaiTi, KaiTi_GB2312, DFKai-SB, "TW\-Kai", serif;}.f16 {font-size: 16px;}.fb {font-weight: bold;}</style><rect width="100%" height="100%" rx="3" fill="#33415B" /><g><g><image height="130" width="130" x="10" y="10" xlink:href="${await getWebImage(userInfo.avatar)}"></image><rect x="365" y="20" width="120" height="35" rx="3" fill="#242E3F" /><rect x="495" y="20" width="130" height="35" rx="3" fill="#242E3F" /></g><g fill="white"><text x="150" y="32" style="font-size: 24px;" class="simkai">${userInfo.username}</text><text x="150" y="56" style="font-size: 14px;" class="simkai">最近常玩的游戏</text></g><g fill="#9b9b9b"><text x="370" y="45" class="simsun f16 fb">社区等级</text><text x="500" y="45" class="simsun f16 fb">游戏数量</text></g><g fill="white"><text x="480" y="45" text-anchor="end" class="simsun f16">${userInfo.level}</text><text x="620" y="45" text-anchor="end" class="simsun f16">${userInfo.gameNum}</text></g></g><g>${imgTpl}</g></svg>`;
    return new Response(svgdata, { status: 200, headers: { 'Content-Type': 'image/svg+xml; charset=utf-8', 'Cache-Control': 'public, max-age=86400, s-maxage=86400, stale-while-revalidate=86400', 'x-edgefunctions-cache': 'miss' } });
}

export async function onRequest({request, params, env}) {
    let reqUrl = new URL(request.url);
    let pathname = reqUrl.pathname.split('/');
    if (pathname[1] == "favicon.ico") {
        return new Response("Not Found", {status: 404});
    }
    let steamid = pathname[1] ? pathname[1] : defaultSteamID;
    let cacheKey = new Request('https://cache.key/'+steamid);
    const cache = caches.default;
    let res = await cache.match(cacheKey);
    if (res) {
        res = new Response(res.body, res);
        res.headers.set('x-edgefunctions-cache', 'hit');
        return res;
    }
    let userInfo = await getUserInfo(steamid, 3, env.apikey);
    res = await renderCard(userInfo);
    await cache.put(cacheKey, res.clone());
    return res;
}