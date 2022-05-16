package handler

import (
	"encoding/base64"
	"encoding/json"
	"fmt"
	"io"
	"net/http"
	"os"
	"strconv"
	"strings"
)

type (
	Card struct {
		apiKey    string
		userUrl   string
		lvlUrl    string
		recentUrl string

		SteamID  string
		UserName string
		Avatar   string
		Level    int
		GameNum  int
		Recent   []string
	}
	UserDataRet struct {
		Response struct {
			Players []struct {
				UserName string `json:"personaname"`
				Avatar   string `json:"avatarfull"`
			} `json:"players"`
		} `json:"response"`
	}
	StatsDataRet struct {
		Response struct {
			Badges []struct {
				BadgeID int `json:"badgeid"`
				Level   int `json:"level"`
			} `json:"badges"`
			PlayerLevel int `json:"player_level"`
		} `json:"response"`
	}
	RecentDataRet struct {
		Response struct {
			Games []struct {
				AppID int `json:"appid"`
			} `json:"games"`
		} `json:"response"`
	}
)

func httpGet(url string) ([]byte, bool) {
	resp, err := http.Get(url)
	if err != nil {
		return nil, true
	}
	defer resp.Body.Close()
	if resp.StatusCode == 200 {
		data, _ := io.ReadAll(resp.Body)
		return data, false
	} else {
		return nil, true
	}
}

func getWebImage(url string) string {
	data, err := httpGet(url)
	if err {
		return ""
	}
	return "data:image/jpeg;base64," + base64.RawStdEncoding.EncodeToString(data)
}

func newCard(id string) *Card {
	return &Card{
		apiKey:    os.Getenv("apikey"),
		userUrl:   "https://api.steampowered.com/ISteamUser/GetPlayerSummaries/v0002/?key=%s&steamids=%s",
		lvlUrl:    "https://api.steampowered.com/IPlayerService/GetBadges/v0001/?key=%s&steamid=%s&format=json",
		recentUrl: "https://api.steampowered.com/IPlayerService/GetRecentlyPlayedGames/v0001/?key=%s&steamid=%s&format=json",
		SteamID:   id,
	}
}

func (c *Card) getUserInfo() bool {
	data, err := httpGet(fmt.Sprintf(c.userUrl, c.apiKey, c.SteamID))
	if err {
		return false
	}
	var userData UserDataRet
	_ = json.Unmarshal(data, &userData)
	c.UserName = userData.Response.Players[0].UserName
	// urlSplit := strings.Split(userData.Response.Players[0].Avatar, "/")
	// c.Avatar = getWebImage("https://avatars.st.dl.pinyuncloud.com/" + urlSplit[len(urlSplit)-1])
	c.Avatar = getWebImage(userData.Response.Players[0].Avatar)
	return true
}

func (c *Card) getStatsInfo() bool {
	data, err := httpGet(fmt.Sprintf(c.lvlUrl, c.apiKey, c.SteamID))
	if err {
		return false
	}
	var statsData StatsDataRet
	_ = json.Unmarshal(data, &statsData)
	c.Level = statsData.Response.PlayerLevel
	c.GameNum = 0
	for _, v := range statsData.Response.Badges {
		if v.BadgeID == 13 {
			c.GameNum = v.Level
		}
	}
	return true
}

func (c *Card) getRecentInfo() bool {
	data, err := httpGet(fmt.Sprintf(c.recentUrl, c.apiKey, c.SteamID))
	if err {
		return false
	}
	var recentData RecentDataRet
	_ = json.Unmarshal(data, &recentData)
	for i, v := range recentData.Response.Games {
		if i < 3 {
			// c.Recent = append(c.Recent, getWebImage(fmt.Sprintf("https://media.st.dl.pinyuncloud.com/steam/apps/%d/header.jpg", v.AppID)))
			c.Recent = append(c.Recent, getWebImage(fmt.Sprintf("https://steamcdn-a.akamaihd.net/steam/apps/%d/header.jpg", v.AppID)))
		} else {
			break
		}
	}
	return true
}

func (c *Card) render(w *http.ResponseWriter) {
	tpl := `<svg width="635" height="150" xmlns:xlink="http://www.w3.org/1999/xlink" xmlns="http://www.w3.org/2000/svg"><style>.simsun {font-family: Consolas, "Nimbus Roman No9 L", "Songti SC", "Noto Serif CJK SC", "Source Han Serif SC", "Source Han Serif CN", STSong, "AR PL New Sung", "AR PL SungtiL GB", NSimSun, SimSun, "TW\-Sung", "WenQuanYi Bitmap Song", "AR PL UMing CN", "AR PL UMing HK", "AR PL UMing TW", "AR PL UMing TW MBE", PMingLiU, MingLiU, serif;}.simkai {font-family: Baskerville, Consolas, "Liberation Serif", "Kaiti SC", STKaiti, "AR PL UKai CN", "AR PL UKai HK", "AR PL UKai TW", "AR PL UKai TW MBE", "AR PL KaitiM GB", KaiTi, KaiTi_GB2312, DFKai-SB, "TW\-Kai", serif;}.f16 {font-size: 16px;}.fb {font-weight: bold;}</style><rect width="100%" height="100%" rx="3" fill="#33415B" /><g><g><image height="130" width="130" x="10" y="10" xlink:href="{{.Avatar}}"></image><rect x="365" y="20" width="120" height="35" rx="3" fill="#242E3F" /><rect x="495" y="20" width="130" height="35" rx="3" fill="#242E3F" /></g><g fill="white"><text x="150" y="32" style="font-size: 24px;" class="simkai">{{.UserName}}</text><text x="150" y="56" style="font-size: 14px;" class="simkai">最近常玩的游戏</text></g><g fill="#9b9b9b"><text x="370" y="45" class="simsun f16 fb">社区等级</text><text x="500" y="45" class="simsun f16 fb">游戏数量</text></g><g fill="white"><text x="480" y="45" text-anchor="end" class="simsun f16">{{.Level}}</text><text x="620" y="45" text-anchor="end" class="simsun f16">{{.GameNum}}</text></g></g><g>`
	for i, v := range c.Recent {
		tpl += fmt.Sprintf(`<image height="75" width="155" x="%d" y="65" xlink:href="%s"></image>`, i*160+150, v)
	}
	tpl += `</g></svg>`
	tpl = strings.Replace(tpl, "{{.Avatar}}", c.Avatar, -1)
	tpl = strings.Replace(tpl, "{{.UserName}}", c.UserName, -1)
	tpl = strings.Replace(tpl, "{{.Level}}", strconv.Itoa(c.Level), -1)
	tpl = strings.Replace(tpl, "{{.GameNum}}", strconv.Itoa(c.GameNum), -1)
	(*w).Header().Set("Content-Type", "image/svg+xml; charset=utf-8")
	(*w).Header().Set("Cache-Control", "public, max-age=14400, s-maxage=14400, stale-while-revalidate=14400")
	fmt.Fprint(*w, tpl)
}

func Handler(w http.ResponseWriter, r *http.Request) {
	SteamID := r.URL.Query().Get("SteamID")
	if len(SteamID) <= 16 {
		if SteamID == "favicon.ico" {
			w.WriteHeader(404)
			return
		}
		SteamID = os.Getenv("SteamID")
	}
	card := newCard(SteamID)
	succ := true
	succ = card.getUserInfo()
	if !succ {
		w.WriteHeader(503)
		fmt.Fprint(w, "Network Error")
		return
	}
	succ = card.getStatsInfo()
	if !succ {
		w.WriteHeader(503)
		fmt.Fprint(w, "Network Error")
		return
	}
	succ = card.getRecentInfo()
	if !succ {
		w.WriteHeader(503)
		fmt.Fprint(w, "Network Error")
		return
	}
	card.render(&w)
}
