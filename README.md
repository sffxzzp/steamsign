# SteamSign

**中文**

EdgeOne 版 Steam 签名档生成工具。由于 KV 存储仍在审核中，目前使用 Cache API 实现缓存，稳定性可能不如 KV。

**示例地址**（中国大陆网络访问可能返回 401）：

https://stsign.edgeone.app/76561198137595648

[![示例图片](https://stsign.edgeone.app/76561198137595648)](https://stsign.edgeone.app/76561198137595648)

**环境变量配置**

- `apikey`：Steam API Key，可[在 Steam 官方网站获取](https://steamcommunity.com/dev)。
- `steamid`：默认显示的 Steam 用户的 **64 位 Steam ID**。可参考 [Steam 官方帮助文档](https://help.steampowered.com/zh-cn/faqs/view/2816-BE67-5B69-0FEC)。

官方似乎已经修复了部署按钮，现在可以一键部署了！

[![使用 EdgeOne Pages 部署](https://cdnstatic.tencentcs.com/edgeone/pages/deploy.svg)](https://edgeone.ai/pages/new?repository-url=https%3A%2F%2Fgithub.com%2Fsffxzzp%2Fsteamsign%2Ftree%2Fedgeone&env=apikey,steamid)

---

**English**

A Steam profile signature generator powered by EdgeOne. Since KV storage is still under review, caching is currently implemented using the Cache API, which may be less reliable than KV.

**Example** (may return HTTP 401 on networks in mainland China):

https://stsign.edgeone.app/76561198137595648

[![Example image](https://stsign.edgeone.app/76561198137595648)](https://stsign.edgeone.app/76561198137595648)

**Environment Variables**

- `apikey`: Your Steam API Key. Obtain one from the [official Steam website](https://steamcommunity.com/dev).
- `steamid`: The **64-bit Steam ID** of the Steam user to display by default. See the [official Steam Support documentation](https://help.steampowered.com/zh-cn/faqs/view/2816-BE67-5B69-0FEC).

The official deployment button appears to have been fixed, so you can now deploy with a single click!

[![Deploy to EdgeOne Pages](https://cdnstatic.tencentcs.com/edgeone/pages/deploy.svg)](https://edgeone.ai/pages/new?repository-url=https%3A%2F%2Fgithub.com%2Fsffxzzp%2Fsteamsign%2Ftree%2Fedgeone&env=apikey,steamid)
