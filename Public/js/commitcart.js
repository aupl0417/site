// XMPP服务器BOSH地址
//var BOSH_SERVICE = 'http://192.168.16.34:7070/http-bind/';
var BOSH_SERVICE = 'http://121.8.156.234:7070/http-bind/';
// XMPP连接
var connection = null;
// 当前状态是否连接
var connected = false;
// 当前登录的JID
var jid = "游客";
// 连接状态改变的事件
var serverjid="小唐客服1@localhost.localdomain";
var newjosn= {
    "title":"旗舰店在线客服",
    "src":"/Public/images/kefu.png",
    "name":"臣妾做不到啊",
    "date":"19:34:59"
  };
var userjosn= {
  "title":"旗舰店在线客服",
  "src":"/Public/images/defaulticon.png",
  "name":"游客",
  "date":"19:34:59"
};

function onConnect(status) {
  console.log(status)
  if (status == Strophe.Status.CONNFAIL) {
    alert("连接失败！");
  } else if (status == Strophe.Status.AUTHFAIL) {
    alert("登录失败！");
  } else if (status == Strophe.Status.DISCONNECTED) {
    alert("连接断开！");
    connected = false;
  } else if (status == Strophe.Status.CONNECTED) { 
    connected = true;
    // 当接收到<message>节，调用onMessage回调函数
    connection.addHandler(onMessage, null, 'message', null, null, null);
    // 首先要发送一个<presence>给服务器（initial presence）
    connection.send($pres().tree());
  }
}
// 接收到<message>
function onMessage(msg) {
  // 解析出<message>的from、type属性，以及body子元素
  var from = msg.getAttribute('from');
  var type = msg.getAttribute('type');
  var elems = msg.getElementsByTagName('body');

  if (type == "chat" && elems.length > 0) {
    var body = elems[0];  
    var message2 = body.innerHTML;
    var jsons = jQuery.parseJSON(message2);
    var servername = from.split('@')[0]; 
    $('#text_inner').append(
      '<div class="mb15 dis_left"> <img width="50" src="'+newjosn.src+'"> <div class="clearfix dis_centent"> <div class="bg-info"><p class="mb5 text-primary">'+servername+' <span class="fr ml10">'+getDates()+'</span></p> <div >'+jsons.content+'</div></div></div></div>'
    ); 
  } 
  
  return true;
}

$(document).keydown(function(event){
  if( event.keyCode == 13 ){ send() }
}); 

$(document).ready(function() {

    getDatas();
    var pass	=	'123456';
    if(user == '') {
    	user = '222test';
    	pass =	'test';
    }
  // 通过BOSH连接XMPP服务器 
    connection = new Strophe.Connection(BOSH_SERVICE);
    connection.connect(user, pass, onConnect,60,1,''); 
    newjosn.name=jid; 

    ///
    $('#text_inner').scrollTop( $('#text_inner')[0].scrollHeight );

    $('.onchat .chat_tle >span').html( newjosn.title );

    $('.open_close').click(function(){
      connection.disconnect();//关闭函数
      window.open('about:blank','_self');
      window.close();
    })

    $('#chat_sub').click(function(){
      send()
    })

});

function send(){
  if(connected) { 
    // 创建一个<message>元素并发送
    var msg = $msg({ 
      to: serverjid, 
      from: jid, 
      type: 'chat'
    }).c("body", null, "{\"content\":\""+$('#chat_text').val()+"\",\"type\":\"text\"}"); 
    connection.send(msg.tree()); 

  } else {

    alert("与客服点开链接，请刷新！");
  } 

  if( any() ) {
    $('#text_inner').append(
    '<div class="mb15 dis_right"> <img width="50" src="'+userjosn.src+'"> <div class="clearfix dis_centent"> <div class="bg-info"><p class="mb5 text-primary">'+userjosn.name+' <span class="fr ml10">'+getDates()+'</span></p> <div >'+$('#chat_text').val()+'</div></div></div></div>'
    );
  };

  $('#chat_text').val(null);
  $('#chat_text').focus();
  $('#text_inner').scrollTop( $('#text_inner')[0].scrollHeight )
}

function any(){
  var str = document.getElementById('chat_text').value;
  if(/[^\s]+/.test(str)){
    return true
  }else{
    return false
  }
}

function getDates(){
  var date = new Date(); 
  var year = date.getFullYear();
  var month = date.getMonth()+1; //js从0开始取 
  var date1 = date.getDate(); 
  var hour = date.getHours(); 
  var minutes = date.getMinutes(); 
  var second = date.getSeconds();
  
  return  year+"年"+month+"月"+date1+"日"+ hour+":"+minutes;
}


function getDatas(){
  $.getJSON("http://121.8.156.234:9090/plugins/userService/userserviceGetOnlineServerList",
    function(req) {
        //成功时的回调方法
        // var jsonsc = jQuery.parseJSON(req); 
        var code = req.code;
        if(code=='0'){
		  //connection.disconnect();//关闭函数
          $('#server_name').html("暂时没有客服在线！");
        }else{
          var data = req.data;
          var value = data[0];
          serverjid = value.username+"@localhost.localdomain"; 
          $('#server_name').html(value.username+"号 为您服务！");
        } 
    }); 
}
