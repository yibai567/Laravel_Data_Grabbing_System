var chokidar = require('chokidar')
var shell = require('shelljs')
var dateTime = require('date-time')

var watcher = chokidar.watch('/alidata/www/crawl-publish/', {
  ignored: /(^|[\/\\])\../,
  persistent: true
});

watcher
  .on('add', path => {
    console.log(`[${dateTime()}] File ${path} has been added`)
    shell.exec(`./script/syncJs.sh ${path}`)
  })
  .on('change', path => {
    console.log(`[${dateTime()}] File ${path} has been changed`)
    shell.exec(`./script/syncJs.sh ${path}`)
  })
