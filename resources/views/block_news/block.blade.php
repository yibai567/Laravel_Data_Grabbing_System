<!doctype html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>区块链新闻</title>

    <link href="https://discourse-cdn-sjc2.com/standard16/stylesheets/desktop_16_6c8093c5bb903f69ebcf5aed633608cd3f0c96ca.css" media="all" rel="stylesheet" data-target="desktop">

    <link href="https://discourse-cdn-sjc2.com/standard16/stylesheets/desktop_theme_3_604a96563eaf09114496cb2dc5858e4aa9d461f1.css" media="all" rel="stylesheet" data-target="desktop_theme">

    <link rel="preload" href="https://discourse-cdn-sjc2.com/standard16/assets/fontawesome-webfont-2adefcbc041e7d18fcf2d417879dc5a09997aa64d675b7a3c4b6ce33da13f3fe.woff2" as="font" type="font/woff2" crossorigin="">
    <script src="https://code.jquery.com/jquery-3.1.1.min.js"></script>

    <style>
      @font-face {
        font-family: 'FontAwesome';
        src: url('https://discourse-cdn-sjc2.com/standard16/assets/fontawesome-webfont-2adefcbc041e7d18fcf2d417879dc5a09997aa64d675b7a3c4b6ce33da13f3fe.woff2') format('woff2'),
             url('https://discourse-cdn-sjc2.com/standard16/assets/fontawesome-webfont-ba0c59deb5450f5cb41b3f93609ee2d0d995415877ddfa223e8a8a7533474f07.woff') format('woff');
      }
    </style>
</head>

<body class="docked navigation-topics" style="overflow-y:auto;overflow-x:hidden;">
    <section id="main" class="ember-application">
        <div id="ember693" class="ember-view">
            <span id="ember727" class="ember-view"></span>
            <div id="ember740" class="ember-view">
            <header class="d-header clearfix">
                <div class="wrap">
                    <div class="contents clearfix">
                        <div class="title">
                            <a href="/" data-auto-route="true">
                            <img src="https://forum.bitcore.io/uploads/bitcore/original/1X/12024701fe4838135378269790d8492fb6849978.png" alt="Bitcore Forum" id="site-logo" class="logo-big">
                            </a>
                        </div>
                        <div class="panel clearfix">

                            <span class="header-buttons">
                                @guest
                                <a href="http://{{$_SERVER['HTTP_HOST']}}/login" target="_blank">
                                <button class="widget-button btn btn-primary btn-small sign-up-button btn-text">
                                    <span class="d-button-label">登陆</span>
                                </button>
                                </a>
                               <a href="http://{{$_SERVER['HTTP_HOST']}}/register" target="_blank">
                                <button class="widget-button btn btn-primary btn-small login-button btn-icon-text">
                                    <i class="fa fa-user d-icon d-icon-user" aria-hidden="true"></i>
                                    <span class="d-button-label">注册</span>
                                </button>
                                </a>

                                @else
                                <button class="widget-button btn btn-primary btn-small sign-up-button btn-text">
                                    <span class="d-button-label">{{ Auth::user()->name }}</span>
                                </button>
                                <a href="http://{{$_SERVER['HTTP_HOST']}}/logout">
                                <button class="widget-button btn btn-primary btn-small sign-up-button btn-text">
                                    <span class="d-button-label">退出</span>
                                </button>
                                </a>
                                @endguest
                             </span>
<!--                             <ul role="navigation" class="icons d-header-icons clearfix">
                                <li class="header-dropdown-toggle">
                                    <a href="/search" data-auto-route="true" title="search topics, posts, users, or categories" aria-label="search topics, posts, users, or categories" id="search-button" class="icon btn-flat">
                                        <i class="fa fa-search d-icon d-icon-search" aria-hidden="true"></i>
                                    </a>
                                </li>
                                <li class="header-dropdown-toggle">
                                    <a href="" data-auto-route="true" title="go to another topic list or category" aria-label="go to another topic list or category" id="toggle-hamburger-menu" class="icon btn-flat">
                                        <i class="fa fa-bars d-icon d-icon-bars" aria-hidden="true"></i>
                                    </a>
                                </li>
                            </ul>
 -->                        </div>
                    </div>
                </div>
            </header>
            </div>

            <span id="ember741" class="ember-view"></span>
            <div id="main-outlet" class="wrap">
                <div class="container">
                    <div id="ember746" class="ember-view"></div>
                    <div id="ember751" class="controls ember-view"></div>
                    <div id="ember757" class="ember-view"></div>
                    <div id="ember760" class="hidden create-topics-notice ember-view"></div>
                </div>
                <div class="container">
                    <div id="ember768" class="ember-view"></div>
                </div>
                <div class="list-controls">
                    <div class="container">
                        <section id="ember774" class="navigation-container ember-view">
                            <ol id="ember788" class="category-breadcrumb ember-view">
                                <li id="ember813" class="select-kit single-select combobox combo-box category-drop is-below is-left-aligned has-reached-minimum bullet ember-view">
                                    <div title="null" aria-haspopup="" aria-label="null" tabindex="0" id="ember826" class="select-kit-header combo-box-header category-drop-header is-none ember-view">
                                        <span class="selected-name">
                                        <span class="category-name" onclick="displayDate()">all companies</span>
                                        </span>
                                        <i class="fa fa-caret-right d-icon d-icon-caret-right caret-icon fa-fw"></i>
                                    </div>
                                    <div id="abc" class="select-kit-body" style="left: 0px; bottom: auto; top: 34px; right: unset;">
                                        <div id="ember833" class="select-kit-filter is-hidden ember-view">
                                            <input autocapitalize="off" autocorrect="off" type="text" autocomplete="off" tabindex="-1" spellcheck="false" placeholder="Search..." id="ember842" class="filter-input ember-text-field ember-view">

                                            <i class="fa fa-search d-icon d-icon-search filter-icon"></i>
                                        </div>

                                        <ul id="ul" class="select-kit-collection ember-view">
                                            <div class="collection-header"></div>
                                            @foreach($data['companies'] as $value)
                                            <li data-guid="ember1163" class="select-kit-row category-row is-highlighted ember-view">
                                                <div class="category-status">
                                                    <span class="badge-wrapper bullet">
                                                        <span class="badge-category-bg" style="background-color: #BD1900;"></span>
                                                        <span data-drop-close="true" class="badge-category clear-badge" title="Ask questions, give feedback, and discuss all things Bitcore.">
                                                            <span class="category-name"><a href="http://{{$_SERVER['HTTP_HOST']}}/block_news?requirement_id={{$value['id']}}">{{$value['name']}}</a></span>
                                                        </span>
                                                    </span>
                                                    <span class="topic-count">{{$value['block_news_total']}}</span>
                                                    <a href="{{$value['list_url']}}" target="_blank"><span>原始文章列表</span></a>
                                                </div>
                                            </li>
                                            @endforeach
                                        </ul>
                                    </div>

                                    <div class="select-kit-wrapper" style="width: 116.984px; height: 31px;"></div>
                                </li>
                                <span id="ember843" class="ember-view"></span>
                                <div class="clear"></div>
                            </ol>
                            <ul id="navigation-bar" class="nav nav-pills ember-view">
                                <li class="active ember-view">
                                    @if(!empty($data['requirement_id']))
                                    <a href="http://{{$_SERVER['HTTP_HOST']}}/block_news?requirement_id={{$data['requirement_id']}}&offset={{$data['offset']}}&limit={{$data['limit']}}&order=show_time&sort=asc">Asc</a>
                                    @else
                                    <a href="http://{{$_SERVER['HTTP_HOST']}}/block_news?offset={{$data['offset']}}&limit={{$data['limit']}}&order=show_time&sort=asc">Asc</a>
                                    @endif
                                </li>
                                <li class="active ember-view">
                                    @if(!empty($data['requirement_id']))
                                    <a href="http://{{$_SERVER['HTTP_HOST']}}/block_news?requirement_id={{$data['requirement_id']}}&offset={{$data['offset']}}&limit={{$data['limit']}}&order=show_time&sort=desc">Desc</a>
                                    @else
                                    <a href="http://{{$_SERVER['HTTP_HOST']}}/block_news?offset={{$data['offset']}}&limit={{$data['limit']}}&order=show_time&sort=desc">Desc</a>
                                    @endif
                                </li>
                                <li id="ember861" class="ember-view"></li>
                            </ul>
                        </section>
                    </div>
                </div>
                <div id="ember867" class="loading-container ember-view"></div>

                <div class="container list-container ">
                  <div class="row">
                    <div class="full-width">
                      <div id="header-list-area"></div>
                    </div>
                  </div>
                  <div class="row">
                    <div class="full-width">
                      <div id="list-area">
                        <span id="ember868" class="ember-view">
                            <div id="ember874" class="discovery-list-container-top-outlet discourse-adplugin ember-view"></div>
                        </span>
                        <div id="ember882" class="bulk-select-container ember-view"></div>

                        <div id="ember890" class="contents ember-view">
                            <table id="ember895" class="topic-list ember-view">
                                <thead>
                                    <tr>
                                        <th data-sort-order="default" class="default">标题</th>
                                        <th data-sort-order="category" class="category sortable">公司</th>
                                        <th data-sort-order="views" class="views sortable num">阅读数</th>
                                        <th data-sort-order="activity" class="activity sortable num">时间</th>
                                    </tr>
                                </thead>
                                <tbody id = "tr">
                                    @foreach($data['block_news'] as $value)
                                    <tr data-topic-id="1974" class="topic-list-item category-bitcore-wallet ember-view">
                                        <td class="main-link clearfix" colspan="1">
                                            <span class="link-top-line">
                                                <a href="{{$value['detail_url']}}" target="_blank" class="title raw-link raw-topic-link">{{$value['title']}}</a>
                                                <span class="topic-post-badges"></span>
                                            </span>
                                        </td>

                                        <td class="category">
                                            <a class="badge-wrapper bullet" href="/c/bitcore-wallet">
                                                <span class="badge-category-bg" style="background-color: #0E76BD;"></span>
                                                <span data-drop-close="true" class="badge-category clear-badge" >
                                                    <span class="category-name">{{$value['corporate_name']}}</span>
                                                </span>
                                            </a>
                                        </td>

                                        <td class="num views ">
                                            <span class="number" title="this topic has been viewed 12 times">{{$value['read_count']}}</span>
                                        </td>

                                        <td class="num age activity" >
                                            <a class="post-activity" href="/t/signing-a-bws-request-from-python/1974/1">
                                                <span class="relative-date" data-time="1528462617404" data-format="tiny">{{$value['show_time']}}</span>
                                            </a>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <footer class="topic-list-bottom">
                          <div id="next" class="loading-container ember-view"></div>
                        </footer>
                      </div>
                    </div>
                  </div>
                </div>

                <span id="ember975" class="ember-view"></span>
                <div id="user-card" class="show-badges no-bg ember-view"></div>
                <div id="group-card" class="no-bg show-badges ember-view"></div>
            </div>

            <span id="ember996" class="ember-view"></span>
            <span id="ember997" class="ember-view"></span>

            <div data-keyboard="false" id="discourse-modal" class="hidden modal d-modal fixed-modal ember-view">
                <div class="modal-outer-container">
                  <div class="modal-middle-container">
                        <div class="modal-inner-container">
                          <div class="modal-header">
                            <div class="modal-close">
                                <a class="close" data-ember-action="" data-ember-action-1004="1004">
                                    <i class="fa fa-times d-icon d-icon-times"></i>
                                </a>
                            </div>
                            <div class="title">
                                <h3></h3>
                            </div>
                          </div>
                          <div id="modal-alert"></div>
                          <div id="ember1033" class="ember-view"></div>
                        </div>
                  </div>
                </div>
            </div>
            <div id="topic-entrance" class="hidden ember-view">
                <button id="ember1011" class="full jump-top btn no-text ember-view">
                    <i class="fa fa-caret-up d-icon d-icon-caret-up"></i> Invalid date
                </button>
                <button id="ember1012" class="full jump-bottom btn no-text ember-view">
                  Invalid date
                    <i class="fa fa-caret-down d-icon d-icon-caret-down"></i>
                </button>
            </div>

            <div id="reply-control" class="closed show-preview ember-view processed">
                <div class="grippie"></div>
            </div>
        </div>
    </section>
    <div id="offscreen-content"></div>
    <form id="hidden-login-form" method="post" action="/login" style="display: none;">
        <input name="username" type="text" id="signin_username">
        <input name="password" type="password" id="signin_password">
        <input name="redirect" type="hidden">
        <input type="submit" id="signin-button" value="Log In">
    </form>
    <script type="text/javascript">
        function displayDate() {
            document.getElementById("abc").style.display="block";
        }
    </script>
    <script type="text/javascript">
        var busy = true
        $(window).scrollTop(0)
        var offset = "{{$data['offset']}}";
        $(window).unbind("scroll").bind("scroll",function(){
            busy = false
        // if(($(window).scrollTop() + $(window).height() > $(document).height() - 100) && !busy)// 接近底部100px
        if ($(window).scrollTop() >= $(document).height() - $(window).height() && !busy)
        {
            $('#next').html('加载中...');
            offset = Number(offset) + 20;
            $.ajax({
                    type: "get",
                    url: "http://webmagic.jinse.cn/ajax_block_news?offset="+offset+"&limit=20",
                    data : "",
                    crossDomain: true,
                    xhrFields: {
                        withCredentials: true
                    },
                    dataType: "json",
                    contentType: "application/json; charset=utf-8"
                }).done(function (d) {
                    // console.log(d);
                    $('#next').hide()
                    busy = true
                    if (d && d.data.length > 0) {
                        console.log(d);
                        var content = "";
                        for (var i = 0, len = d.data.length; i < len; i++) {
                            content += '<tr data-topic-id="1974" class="topic-list-item category-bitcore-wallet ember-view"><td class="main-link clearfix" colspan="1"><span class="link-top-line"><a href="{{$value['detail_url']}}" target="_blank" class="title raw-link raw-topic-link">' + d.data[i].title + '</a><span class="topic-post-badges"></span></span></td><td class="category"><a class="badge-wrapper bullet" href="/c/bitcore-wallet"><span class="badge-category-bg" style="background-color: #0E76BD;"></span><span data-drop-close="true" class="badge-category clear-badge" ><span class="category-name">' +d.data[i].corporate_name+ '</span></span></a></td><td class="num views "><span class="number" >' + d.data[i].read_count + '</span></td><td class="num age activity" ><a class="post-activity" href="/t/signing-a-bws-request-from-python/1974/1"><span class="relative-date" data-time="1528462617404" data-format="tiny">' + d.data[i].show_time +'</span></a></td></tr>'
                        }
                        console.log(offset);
                        $('#tr').append(content);
                    } else {
                        console.log('空')
                        busy = true
                    }
                    }).fail(function () {
                        busy = true
                        $('#next').hide()
                        $('#next').html('没有数据');
                    })
                }
            });
    </script>

</body>
</html>
