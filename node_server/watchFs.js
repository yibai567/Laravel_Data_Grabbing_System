var chokidar = require('chokidar')
var shell = require('shelljs')
var dateTime = require('date-time')

var watcher = chokidar.watch('/alidata/www/jinse-webmagic/resources/script', {
  ignored: /(^|[\/\\])\../,
  persistent: true
});

// var log = console.log.bind(console);
watcher
  .on('add', path => {
    console.log(`[${dateTime()}] File ${path} has been added`)
    shell.exec(`./script/shsyncJs.sh ${path}`)
  })
  .on('change', path => {
    console.log(`[${dateTime()}] File ${path} has been changed`)
    shell.exec(`./script/syncJs.sh ${path}`)
  })
