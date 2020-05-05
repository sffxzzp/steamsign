from PIL import Image, ImageDraw, ImageFont
import requests, json, sys
from io import BytesIO

class steamsign:
	def __init__(self, steamid=0, small=True, apikey='824367C3B8AA3C7EADD70FF8A0DB3516'):
		self.ttf = 'https://cdn.jsdelivr.net/gh/googlefonts/noto-cjk/NotoSansCJK-Regular.ttc'
		self.steamid = steamid
		self.small = small
		self.apikey = apikey
		self.data = {}
		self.api = 'http://api.steampowered.com'
		self.web = requests.session()
		self.getWebFont()
		self.getInfo()
		self.makeSign()
		self.saveSign()
	def getWebFont(self):
		if 'http' in self.ttf:
			with open(self.ttf.split('/')[-1], 'wb') as font:
				font.write(self.web.get(self.ttf).content)
			self.ttf = self.ttf.split('/')[-1]
	def getInfo(self):
		data = json.loads(self.web.get('%s/ISteamUser/GetPlayerSummaries/v0002/?key=%s&steamids=%s' % (self.api, self.apikey, self.steamid)).text)
		self.data['name'] = data['response']['players'][0]['personaname']
		self.data['head'] = data['response']['players'][0]['avatarfull']
		data = json.loads(self.web.get('%s/IPlayerService/GetBadges/v0001/?key=%s&steamid=%s&format=json' % (self.api, self.apikey, self.steamid)).text)
		self.data['level'] = str(data['response']['player_level'])
		for badge in data['response']['badges']:
			if badge['badgeid'] == 13:
				self.data['count'] = str(badge['level'])
				break
		data = json.loads(self.web.get('%s/IPlayerService/GetRecentlyPlayedGames/v0001/?key=%s&steamid=%s&format=json' % (self.api, self.apikey, self.steamid)).text)
		self.data['recent'] = []
		for recent in data['response']['games'][:3]:
			self.data['recent'].append(str(recent['appid']))
	def getNetPic(self, link):
		ioobj = BytesIO()
		ioobj.write(self.web.get(link).content)
		return ioobj
	def getTxtSize(self, text, size):
		return ImageFont.truetype(self.ttf, size).getsize(text)
	def makeSign(self):
		# Init
		imgWidth = 635 if self.small else 655
		imgHeight = 150 if self.small else 180
		self.image = Image.new('RGB', (imgWidth, imgHeight), (51, 66, 90))
		draw = ImageDraw.Draw(self.image)
		# Init colors
		cWhite = (255, 255, 255)
		cDeepBlue = (36, 46, 63)
		cGrey = (155, 155, 155)
		# Draw head pic
		headSize = (130, 130) if self.small else (160, 160)
		headPos = int((imgHeight - headSize[0]) / 2)
		headPos = (headPos, headPos)
		head = Image.open(self.getNetPic(self.data['head'])).resize(headSize)
		self.image.paste(head, headPos)
		# Draw username
		nameFontSize = 25
		nameSize = self.getTxtSize(self.data['name'], nameFontSize)
		namePos = (imgHeight, headPos[1]-5)
		draw.text(namePos, self.data['name'], font=ImageFont.truetype(self.ttf, nameFontSize), fill=cWhite)
		# Draw level
		tagFontSize = 16
		lvlTag = '社区等级'
		lvlTagSize = self.getTxtSize(lvlTag, tagFontSize)
		lvlTxtSize = self.getTxtSize(self.data['level'], tagFontSize)
		lvlImgSize = (lvlTagSize[0]+lvlTxtSize[0]+20, lvlTagSize[1]+10)
		lvlImg = Image.new('RGB', lvlImgSize, cDeepBlue)
		lvlImgDraw = ImageDraw.Draw(lvlImg)
		lvlImgDraw.text((5, 5), lvlTag, font=ImageFont.truetype(self.ttf, tagFontSize), fill=cGrey)
		lvlImgDraw.text((15+lvlTagSize[0], 5), self.data['level'], font=ImageFont.truetype(self.ttf, tagFontSize), fill=cWhite)
		# Draw game count
		cntTag = '游戏数量'
		cntTagSize = self.getTxtSize(cntTag, tagFontSize)
		cntTxtSize = self.getTxtSize(self.data['count'], tagFontSize)
		cntImgSize = (cntTagSize[0]+cntTxtSize[0]+20, cntTagSize[1]+10)
		cntImg = Image.new('RGB', cntImgSize, cDeepBlue)
		cntImgDraw = ImageDraw.Draw(cntImg)
		cntImgDraw.text((5, 5), cntTag, font=ImageFont.truetype(self.ttf, tagFontSize), fill=cGrey)
		cntImgDraw.text((15+cntTagSize[0], 5), self.data['count'], font=ImageFont.truetype(self.ttf, tagFontSize), fill=cWhite)
		# Handle level and game count panel position
		if self.small:
			cntImgPos = (imgWidth-headPos[0]-cntImgSize[0], namePos[1]+int(nameSize[1]/2))
			lvlImgPos = (cntImgPos[0]-lvlImgSize[0]-10, cntImgPos[1])
		else:
			lvlImgPos = (namePos[0], namePos[1]+nameSize[1]+5)
			cntImgPos = (lvlImgPos[0]+lvlImgSize[0]+10, lvlImgPos[1])
		# Paste level and game count
		self.image.paste(cntImg, cntImgPos)
		self.image.paste(lvlImg, lvlImgPos)
		# Draw recent pics
		recRWH = 215/460
		recPadding = 5
		recWidth = int((imgWidth-imgHeight-2*recPadding-headPos[0])/3)
		recHeight = int(recWidth*recRWH)
		recSize = (recWidth, recHeight)
		recPos = (namePos[0], imgHeight-recSize[1]-headPos[1])
		for appid in self.data['recent']:
			appPic = Image.open(self.getNetPic('https://steamcdn-a.akamaihd.net/steam/apps/%s/header.jpg' % appid)).resize(recSize)
			self.image.paste(appPic, recPos)
			recPos = (recPos[0]+recSize[0]+recPadding, recPos[1])
		# Draw recent text
		recTag = '最近常玩的游戏'
		recTagSize = self.getTxtSize(recTag, tagFontSize)
		recTagPos = (namePos[0], recPos[1]-recTagSize[1]-5)
		draw.text(recTagPos, recTag, font=ImageFont.truetype(self.ttf, tagFontSize), fill=cWhite)
	def saveSign(self):
		self.image.save('%s.jpg' % self.steamid, 'jpeg')

if __name__ == '__main__':
	steamid, small = sys.argv[1:3]
	small = False if small == '0' else True
	steamsign(steamid, small)