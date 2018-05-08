var utilJS = require('../../public/util')
var configJS = require('../../public/config')
var Casper = require('casper')

var config = {
    verbose: {{verbose}},
    logLevel: "{{log_level}}",
    viewportSize: {
        width: {{width}},
        height: {{height}},
    },
    pageSettings: {
        loadImages: {{load_images}},
        loadPlugins: {{load_plugins}},
    },
}

var casper = Casper.create(config)
var startOn = utilJS.getDate()

