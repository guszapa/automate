var timeDiff = null;
var timeStart = null;

var timersUp = new Array();
var timersDown = new Array();
var ressis = new Array();

function startCounter() {
    timeStart = parseInt($('#servertime').attr('time'));
    timeDiff = serverTime - timeStart;

    field = document.getElementsByTagName('span');
    for(i = 0; i < field.length; i++){
        if (field[i].className == 'countdown'){
            timersDown.push(field[i]);
        }
        else if (field[i].className == 'countup'){
            timersUp.push(field[i]);
        }
    }
    ressis.push($('wood'));
    ressis.push($('stone'));
    ressis.push($('iron'));

    counterTick(); 
    window.setInterval("counterTick()", 1000);
}

function counterTick() {
    currTime = serverTime++;
    currTimeDiff = currTime - timeStart;
    currTimeDiffReal = currTime - (timeStart + timeDiff);

    for (var i=0; i<timersUp.length; i++) {
        timer = timersUp[i];
        timer.innerHTML = localTimestampToTime(parseInt(timer.getAttribute('time')) + currTimeDiffReal);
    }

    for (var i=0; i<timersDown.length; i++) {
        timer = timersDown[i];

        if (parseInt(timer.getAttribute('time')) < 0) {
            continue;
        }
        now = parseInt(timer.getAttribute('time')) - currTimeDiffReal;
        if (now <= 0) {
            if (timer.getAttribute('reload') == 'true') {
                timer.innerHTML = timestampToTime(0);
                setTimeout("location.reload();", 1000);
                return;
            }
            else{
                timer.innerHTML = timestampToTime(0);
            }
        }
        else{
            timer.innerHTML = timestampToTime(now, true);
        }
    }
}

function localTimestampToTime(timestamp) {
    timeString = '';
    t_time = new Date(timestamp * 1000);

    h = t_time.getHours();
    m = t_time.getMinutes();
    s = t_time.getSeconds();

    timeString += h + ':';
    if(m < 10) {
        timeString += '0';
    }
    timeString += m + ':';
    if(s < 10) {
        timeString += '0';
    }
    timeString += s;

    return timeString;
}

function timestampToTime(timestamp, sdays) {
    timeString = '';

    h = Math.floor(timestamp/3600);
    m = Math.floor(timestamp/60) % 60;
    s = timestamp % 60;

    if (h >= 24){
        d = Math.floor(h / 24);
        h = h % 24;
        if (sdays) {
            timeString += (d == 1) ? d + ' ' + lang['DAY'] +' ' : d + ' ' + lang['DAYS'] +' ';
        }
    }
    timeString += h + ':';
    if(m < 10) {
        timeString += '0';
    }
    timeString += m + ':';
    if(s < 10) {
        timeString += '0';
    }
    timeString += s;

    return timeString;
}

function formatNum(num) {
    var num = '' + num;
    var laenge = num.length;
    if (laenge > 3) {
        var mod = laenge % 3;
        var output = (mod > 0 ?
        (num.substring(0,mod)) : '');
        for (var i=0 ; i < Math.floor(laenge / 3); i++) {
            if ((mod == 0) && (i == 0)) {
                output += num.substring(mod+ 3 * i, mod + 3 * i + 3);
            } else {
                output+= '.' + num.substring(mod + 3 * i, mod + 3 * i + 3);
            }
        }
        return output;
    }
    return num;
}

function insertNum(formname, name, num) {
    formname = (formname) ? formname : 0;
    elem = document.forms[formname].elements[name];
    if (elem.value == num) {
        elem.value = '0';
    }
    else {
        elem.value = num;
    }
}

function propagateMoral(moral) {
    opener.document.forms['kingsage'].elements['moral'].value = moral;
}

function propagateTarget(x, y) {
    opener.document.forms['kingsage'].elements['send_x'].value = x;
    opener.document.forms['kingsage'].elements['send_y'].value = y;
    window.close();
}

function popup_mod(url, width, height) {
    handle = window.open(url, "popup", "width=" + width + ",height=" + height + ",left=100,top=100,resizable=yes,scrollbars=yes,location=no");
    handle.focus();
}

function checkall(masterbox, name){
    f = masterbox.form;
    for(i=0; i < f.elements.length;i++){
        if (f.elements[i].name == name){
            f.elements[i].checked = masterbox.checked;
        }
    }
}

function ajaxRequest(url, data, callback) {
    try {
        // Moz supports XMLHttpRequest. IE uses ActiveX.
        // browser detction is bad. object detection works for any browser
        xmlhttp = window.XMLHttpRequest ? new XMLHttpRequest() : new ActiveXObject("Microsoft.XMLHTTP");
    } catch (e) {
        // browser doesn't support ajax. handle however you want
        return;
    }

    async = (callback != null) ? true : false;

    if (data != null) {
        xmlhttp.open("POST", url, async);
        xmlhttp.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
        xmlhttp.setRequestHeader("Content-length", data.length);
    }
    else {
        xmlhttp.open("GET", url, async);
    }

    if (async) {
        xmlhttp.onreadystatechange = function() {
            if ((xmlhttp.readyState == 4) && (xmlhttp.status == 200)) {
                callback.complete(xmlhttp);
            }
        }
    }
    xmlhttp.send(data);

    return xmlhttp;
}

function quickEditActivate(qeLabel, qeEdit) {
    $(qeEdit).style.display = '';
    $(qeLabel).style.display = 'none';
}

function quickEdit(qeLabel, qeText, qeEdit, qeForm, url) {
    var data = $(qeForm).value;
    var encData = encodeURIComponent(data);
    ajaxRequest(url, 'text=' + encData);

    $(qeText).innerHTML = data;
    $(qeEdit).style.display = 'none';
    $(qeLabel).style.display = '';
}

//function $(id) {
//    return document.getElementById(id);
//}

function resizeTextarea(name, inc) {
    field = $(name);
    curr_size = parseInt(field.getAttribute('rows'));
    if (inc < 0 && curr_size > 4 || inc > 0) {
        field.setAttribute('rows',    curr_size + inc);
    }
}

function messageAnswer() {
    $('reply1').style.display = '';
    $('reply2').style.display = '';
    $('reply_naviTabbed').style.display = 'none';
}

var max = true;
function selectCoiningNoneMax(t_max, t_nothing) {
    form = document.forms['kingsage'];

    for(var i = 0; i < form.elements.length; i++) {
        var select = form.elements[i];
        if (select.selectedIndex != null) {
            if (max) {
                select.selectedIndex = select.length - 1;
            }
            else{
                select.value = 0;
            }
        }
    }

    text = max ? t_nothing : t_max;
    $('select_all_1').innerHTML = text;
    $('select_all_2').innerHTML = text;

    max = max ? false : true;
    countCoins();
}

function countCoins() {
    form = document.forms['kingsage'];

    sum = 0;
    for(var i = 0; i < form.elements.length; i++) {
        var select = form.elements[i];
        if (select.selectedIndex != null) {
            sum += parseInt(select.value);
        }
    }

    $('select_count_1').innerHTML = sum;
    $('select_count_2').innerHTML = sum;

}

function setTradeXY(select) {
    if (select.selectedIndex != null) {
        var xy = select.value.split('|');
    }

    if (isFinite(xy[0]) && isFinite(xy[1])) {
        document.forms['kingsage'].elements['send_x'].value = xy[0];
        document.forms['kingsage'].elements['send_y'].value = xy[1];
    }
}

function setCompassDirection(direction) {
    document.forms['kingsage'].elements[direction].checked = true;
}

function switchDisplay(name) {
    var o = $(name);
    o.style.display = (o.style.display == 'none' ? '' : 'none');
}

function setPreviewColor() {
    hex="0123456789ABCDEF";

    r = $('color_red').value;
    g = $('color_green').value;
    b = $('color_blue').value;
    color = hex.charAt(b % 16);
    b = b >> 4;
    color = hex.charAt(b % 16) + color;
    color = hex.charAt(g % 16) + color;
    g = g >> 4;
    color = hex.charAt(g % 16) + color;
    color = hex.charAt(r % 16) + color;
    r = r >> 4;
    color = hex.charAt(r % 16) + color;

    $('preview_color').style.background = '#' + color;
}

function setColorValues(color) {
    r = parseInt(color.substr(1, 2), 16);
    g = parseInt(color.substr(3, 2), 16);
    b = parseInt(color.substr(5, 2), 16);
    $('color_red').value = r;
    $('color_green').value = g;
    $('color_blue').value = b;
    $('preview_color').style.background = color;
}

function editColorType(type, name, color) {
    document.forms['kingsage'].elements['object_type'][type].selected = true;
    document.forms['kingsage'].elements['object_name'].value = name;
    setColorValues(color);
}

var ajaxLoaded = new Array();

function formatTime(seconds) {
    var timeString = '';

    var h = Math.floor(seconds / 3600);
    seconds = seconds % 3600;
    var m = Math.floor(seconds / 60);
    seconds = seconds % 60;
    var s = seconds;

    timeString += h + ":";
    if (m < 10) {
        timeString += "0";
    }
    timeString += m + ":";
    if (s < 10) {
        timeString += "0";
    }
    timeString += s;

    return timeString;
}

function showBBEdit() {
    $('bb_show1').style.display = 'none';
    $('bb_show2').style.display = 'none';
    $('bb_edit1').style.display = '';
    $('bb_edit2').style.display = '';
}

function switchRightsFlags() {
    $('f_head').disabled = $('f_admin').checked;
    $('f_invite').disabled = $('f_head').checked;
    $('f_diplomacy').disabled = $('f_head').checked;
    $('f_rm').disabled = $('f_head').checked;
    $('f_mod').disabled = $('f_head').checked;
    $('f_showall').disabled = $('f_head').checked;

    if ($('f_admin').checked) {
        $('f_head').checked = true;
    }
    if ($('f_head').checked) {
        $('f_invite').checked = true;
        $('f_diplomacy').checked = true;
        $('f_rm').checked = true;
        $('f_mod').checked = true;
        $('f_showall').checked = true;
    }
}

function insertDeffSurvive(spear, sword, axe, bow, spy, light, heavy, ram, kata, snob, wall, bkata) {
    setSimUnitsDeff(spear, sword, axe, bow, spy, light, heavy, ram, kata, snob);
    $('wall').value = wall;
    $('kata_target_buildlevel').value = bkata;
}

function showDynPopup(name, url) {
    $('receiver_content').innerHTML = ajaxRequest(url, null).responseText;
    $(name).style.display='';
}

function hideDynPopup(name) {
    $(name).style.display='none';
}

function addMailReceiver(name) {
    receivers = $('msg_to').value;
    if (receivers == '') {
        $('msg_to').value = name;
    }
    else{
        $('msg_to').value = receivers + ';' + name;
    }
}

function clearMailReceivers() {
    $('msg_to').value = '';
}

function setSimUnitsAtt(spear, sword, axe, bow, spy, light, heavy, ram, kata, snob) {
    $('att_spear').value = (spear > 0 ? spear : '');
    $('att_sword').value = (sword > 0 ? sword : '');
    $('att_axe').value = (axe > 0 ? axe : '');
    $('att_bow').value = (bow > 0 ? bow : '');
    $('att_spy').value = (spy > 0 ? spy : '');
    $('att_light').value = (light > 0 ? light : '');
    $('att_heavy').value = (heavy > 0 ? heavy : '');
    $('att_ram').value = (ram > 0 ? ram : '');
    $('att_kata').value = (kata > 0 ? kata : '');
    $('att_snob').value = (snob > 0 ? snob : '');
}

function setSimUnitsDeff(spear, sword, axe, bow, spy, light, heavy, ram, kata, snob) {
    $('def_spear').value = (spear > 0 ? spear : '');
    $('def_sword').value = (sword > 0 ? sword : '');
    $('def_axe').value = (axe > 0 ? axe : '');
    $('def_bow').value = (bow > 0 ? bow : '');
    $('def_spy').value = (spy > 0 ? spy : '');
    $('def_light').value = (light > 0 ? light : '');
    $('def_heavy').value = (heavy > 0 ? heavy : '');
    $('def_ram').value = (ram > 0 ? ram : '');
    $('def_kata').value = (kata > 0 ? kata : '');
    $('def_snob').value = (snob > 0 ? snob : '');
}

function storeCookie(name, value) {
    document.cookie = name + '=' + value;
}

function switchMinimap() {
    obj_mm = $('cell_minimap');
    if (obj_mm.style.display == '') {
        obj_mm.style.display = 'none';
        $('cell_minimap2').style.display = 'none';
        storeCookie('minimap_display', 0);
    }
    else {
        img_mm = $('image_minimap');
        img_mm.src = img_mm.getAttribute('url');
        obj_mm.style.display = '';
        $('cell_minimap2').style.display = '';
        storeCookie('minimap_display', 1);
    }
}

var arVersion = navigator.appVersion.split("MSIE");
var version = parseFloat(arVersion[1]);

function fixPNG(myImage) 
{
    if ((version >= 5.5) && (version < 7) && (document.body.filters)) 
    {
       var imgID = (myImage.id) ? "id='" + myImage.id + "' " : "";
       var imgClass = (myImage.className) ? "class='" + myImage.className + "' " : "";
       var imgTitle = (myImage.title) ? "title='" + myImage.title  + "' " : "title='" + myImage.alt + "' ";
       var imgStyle = "display:inline-block;" + myImage.style.cssText;
       var strNewHTML = "<span " + imgID + imgClass + imgTitle
                  + " style=\"" + "width:" + myImage.width 
                  + "px; height:" + myImage.height 
                  + "px;" + imgStyle + ";"
                  + "filter:progid:DXImageTransform.Microsoft.AlphaImageLoader"
                  + "(src=\'" + myImage.src + "\', sizingMethod='scale');\"></span>";
       myImage.outerHTML = strNewHTML;
    }
}

function switch_world_selection() {
    curr_style = $('world_selection').style.display;
    if (curr_style == 'none') {
        $('world_selection').style.display = 'inline';
    }
    else {
        $('world_selection').style.display = 'none';
    }
}

function select_world(code, name) {
    $('world_selection').style.display = 'none';
    $('world_name').innerHTML = name;
    $('server_id').value = code;
}

function replaceInput(obj, id, type, text, color){
    var newO=document.createElement('input');
    newO.setAttribute('type', type);
    newO.setAttribute('name', obj.getAttribute('name'));
    newO.id = id;
    newO.className = 'input';
    newO.style.color = color;
    obj.parentNode.replaceChild(newO,obj);
    newO.focus();
}


function checkInput(id, text, defaulttype) {
    obj = $(id);
    if (defaulttype == 'password') {
        if (obj.value == '' && obj.type == 'password') {
        }
        else {
            if (obj.type == 'text') {
                replaceInput(obj, id, 'password', '', 'black');
                obj = $(id);
                obj.focus();
            }
        }
    }
    if (defaulttype == 'text') {
        if (obj.value == '') {
        }
        else {
            if (obj.value == text) {
                replaceInput(obj, id, 'text', '', 'black');
                obj = $(id);
                obj.focus();
            }
        }
    }
}
