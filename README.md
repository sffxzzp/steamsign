SteamSign
======

EdgeOne 版的 Steam 签名档生成工具，因 KV 还在审核中，所以缓存是用 Cache API 实现，可能没 KV 稳。

示例地址（国内网络可能 401）：[https://stsign.edgeone.app/76561198137595648](https://stsign.edgeone.app/76561198137595648)

[![示例图片](https://stsign.edgeone.app/76561198137595648)](https://stsign.edgeone.app/76561198137595648)

需要设置环境变量 apikey 为 Steam 的 API KEY，可[在此](https://steamcommunity.com/dev)获取。

还需要设置环境变量 steamid，是在不带参数情况下的默认显示的 Steam 用户的 [64 位 ID](https://help.steampowered.com/zh-cn/faqs/view/2816-BE67-5B69-0FEC)。

官方好像把按钮修好了，现在可以一键部署了。

[![使用 EdgeOne Pages 部署](https://cdnstatic.tencentcs.com/edgeone/pages/deploy.svg)](https://edgeone.ai/pages/new?repository-url=https%3A%2F%2Fgithub.com%2Fsffxzzp%2Fsteamsign%2Ftree%2Fedgeone&env=apikey,steamid)
