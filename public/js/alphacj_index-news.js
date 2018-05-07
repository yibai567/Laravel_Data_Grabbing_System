var utilJS = require('../../public/util')
var configJS = require('../../public/config')
var Casper = require('casper')

var config = {
    load_images: {{load_images}},
    load_plugins: {{load_plugins}},
    log_level: {{log_level}},
    verbose: {{verbose}},
    width: {{width}},
    height: {{height}},
}

var casper = Casper.create(config)
var startOn = utilJS.getDate()

