window.addEvent("ready", function(){
    var text = document.getElementById("_text");
    var textContainer = document.getElementById("textContainer");
    var masonry = document.getElementById("masonry");
    text.contentEditable='true';
    var pattern = /((([A-Za-z]{3,9}:(?:\/\/)?)(?:[-;:&=\+\$,\w]+@)?(?:[A-Za-z0-9.-]+:[0-9]{1,5})?[A-Za-z0-9.-]+|(?:www.|[-;:&=\+\$,\w]+@)(?:[A-Za-z0-9.-]+:[0-9]{1,5})?[A-Za-z0-9.-]+)((?:\/[\+~%\/.\w_-]*)?\??(?:[-\+=&;%@.\w_]*)#?(?:[\w@?^\!=%&amp;\/~+#-]*))?)/igm;
    var hasUrl = false;
    var urlTimeout = null;
    var urlObject = null;
    var urlText = "";
    var working__ = false;
    function buttonEnabled(){
        if(text.getText().trim()==""&&!hasUrl)document.getElementById("share").setAttribute("disabled", "disabled");
        else document.getElementById("share").removeAttribute("disabled");
    }
    function divChange(t){
        if(hasUrl||working__)return;
        var t = (t?t:text.getText()).match(pattern);
        if(t&&t[0]){
            if(urlTimeout){
                clearTimeout(urlTimeout);
                urlTimeout = null;
            }
            urlTimeout = setTimeout(function(){
                working__ = true;
                var ts = new Request(window.location.href);
                ts.addData("url",t[0]);
                ts.addEvent("end",function(){
                    working__ = false;
                });
                ts.addEvent("ok",function(txt){
                    if(txt==""||txt=="[]")return;
                    try{
                        var result = JSONDencoder(txt);
                        hasUrl = true;
                        urlText = t[0];
                        if(!result.og)result.og = {};
                        if(!result.og["og:image"])result.og["og:image"] = result.image;
                        if(!result.og["og:title"])result.og["og:title"] = result.title;
                        if(!result.og["og:description"])result.og["og:description"] = result.description;
                        var obj = ('<div class="tbl"><div><div class="tbl obj"><div style="width: 0;" ><div class="img" style="background-image: url(\''+result.og["og:image"]+'\')"></div></div><div class="content"><h3><a href="'+urlText+'">'+result.og["og:title"]+'</a></h3><div class="url">'+result.host+'</div><div class="cnt">'+result.og["og:description"]+'</div></div></div></div><div class="_btn_"><span><i class="fa fa-times-circle" aria-hidden="true"></i><br>Remove</span></div></div>').toElement();
                        obj[0].find("._btn_")[0].addEvent("click",function(){
                            document.getElementById("object").empty();
                            hasUrl = false;
                            urlText = "";
                            buttonEnabled();
                        });
                        document.getElementById("object").appendChild(obj[0]);
                        urlObject = result;
                    }catch(e){}
                });
                ts.send();
            },800);
        }else{
            hasUrl = false;
            try{
                if(urlTimeout){
                    clearTimeout(urlTimeout);
                    urlTimeout = null;
                }
            }catch(e){}
        }
    }
    document.getElementById("share").addEvent("click", function(){
        var i = this.find("i")[0];
        var txt = text.getText().trim();
        if(txt!=""||hasUrl){
            var tmp = '';
            this.setAttribute("disabled", "disabled");
            document.getElementById("cancel").setAttribute("disabled", "disabled");
            var ts = new Request(window.location.href);
            ts.addData("action","post");
            ts.addData("txt",txt);
            txt = txt.replace("\n","<br /><br />")
            i.style.display="initial";
            if(hasUrl){
                tmp = '<div class="tbl"><div><div class="tbl obj"><div style="width: 0;" ><div class="img" style="background-image: url(\''+urlObject.og["og:image"]+'\')"></div></div><div class="content"><h3><a href="'+urlText+'">'+urlObject.og["og:title"]+'</a></h3><div class="url">'+urlObject.host+'</div><div class="cnt">'+urlObject.og["og:description"]+'</div></div></div></div></div>';
                ts.addData("obj",tmp);
            }
            ts.addEvent("end",function(){
                document.getElementById("share").removeAttribute("disabled");
                document.getElementById("cancel").removeAttribute("disabled");
                i.style.display="none";
            });
            ts.addEvent("ok",function(_txt){
                if(_txt=="true"){
                    masonry.insert(('<div class="col"><div class="tbl"><div style="width: 0;"><span class="fa-stack fa-lg"><i class="fa fa-circle fa-stack-2x"></i><i class="fa fa-user fa-stack-1x fa-inverse"></i></span></div><div class="hdr"><div class="_name">Guest</div><div class="_date">Just now</div></div></div><div class="_cnt_">'+txt + tmp +'</div></div>').toElement()[0],1);
                    document.getElementById("cancel").shutEvent("click");
                }
            });
            ts.send();
        }
    })
    document.getElementById("cancel").addEvent("click", function(){
        if(urlTimeout){
            clearTimeout(urlTimeout);
            urlTimeout = null;
        }
        document.getElementById("object").empty();
        hasUrl = false;
        text.empty();
        editable.shutEvent("blur");
        buttonEnabled();
    });
    BK.placeholder(text,"What's on your mind?",function(){
        buttonEnabled();
        divChange(text.getText());
    });
    var animating = false;
    var editable = text.find("[contentEditable]")[0];
    editable.addEvent("focus",function(e){
        if(animating||text.getText().trim()!=""||hasUrl)return;
        var ww = window.getWidth();
        text.setAttribute("data-focused","true");
        animating = true;
        textContainer.appendChild(text.parentNode);
        text.parentNode.style.marginLeft = "0";
        text.parentNode.style.width = ww<1200?"50%":"33.33333%";
        text.parentNode.style.marginBottom = "1.5em";
        var w = document.getElementById("masonry").getWidth();
        var _w = text.parentNode.getWidth();
        var h = (-text.parentNode.getHeight())+56;
        text.parentNode.style.marginBottom = h+"px";
        var t =  new Animate.apply({"marginLeft":((w - _w) / 2)+"px", "width":ww<850?"80%":"40%", "marginBottom":"1.5em"});
        t.iterations = 10;
        t.after(function(){
            text.parentNode.style.marginLeft = "auto";
            text.parentNode.style.marginRight = "auto";
            text.parentNode.style.width = ww<850?"80%":"40%";
            animating = false;
        })
        animation = (new Animate(text.parentNode,t)).start(true);
        editable.focus();
    });
    editable.addEvent("blur",function(e){
        if(animating||text.getText().trim()!=""||hasUrl)return;
        var ww = window.getWidth();
        animating = true;
        text.removeAttribute("data-focused");
        var h = (-text.parentNode.getHeight())+56;
        var t =  new Animate.apply({"marginLeft":"0px", "width":ww<1200?"50%":"33.33333%", "margin-bottom":h+"px"});
        t.iterations = 10;
        t.after(function(){
            text.parentNode.style.marginLeft = "auto";
            text.parentNode.style.marginRight = "auto";
            text.parentNode.style.width = "100%";
            text.parentNode.style.marginBottom = "1.5em";
            masonry.insert(text.parentNode, 0);
            animating = false;
        })
        animation = (new Animate(text.parentNode,t)).start(true);
    });
});