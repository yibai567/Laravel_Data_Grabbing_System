var utilJS = require('../../public/util')
var configJS = require('../../public/config')
var request = require('request-promise');

var startOn = utilJS.getDate()
var indexNews = []
var response = {}
var headers = {}
headers['User-Agent'] = utilJS.getRandomUserAgent()


