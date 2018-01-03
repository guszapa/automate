jQuery(document).ready(function(){
    var serverTime = jQuery('#servertime');

    if (serverTime.length > 0) {
        var time   = serverTime.text().split(':');
        var hour   = parseInt(time[0], 10);
        var minute = parseInt(time[1], 10);
        var second = parseInt(time[2], 10);

        setInterval(function(){
            if (++second == 60) {
                second = 0;

                if (++minute == 60) {
                    minute = 0;

                    if (++hour == 24) {
                        hour = 0;
                    }
                }
            }

            serverTime.text(padTime(hour) + ':' + padTime(minute) + ':' + padTime(second));
        }, 1000);
    }

    function padTime(value)
    {
        if (value < 10) {
            value = '0' + value;
        }

        return value;
    }
    
    startCounter();
});

var timeDiff = null;
var timeStart = null;

var timersUp = new Array();
var timersDown = new Array();
var prograssBars = new Array();
var ressis = new Array();

function startCounter() {
    timeStart = parseInt($('servertime').getAttribute('time'));
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

    field = document.getElementsByTagName('div');
    for(i = 0; i < field.length; i++){
        if (field[i].className == 'progress'){
            prograssBars.push(field[i]);
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

    for (var i=0; i<prograssBars.length; i++) {
        progressbar = prograssBars[i];
        start = parseInt(progressbar.getAttribute('start'));
        end = parseInt(progressbar.getAttribute('end'));
        maxwidth = parseInt(progressbar.getAttribute('maxwidth'));

        currWidth = Math.max(0, Math.min(maxwidth, Math.ceil((currTime - start) / (end - start) * maxwidth)));
//      alert(Math.ceil((currTime - start)) + ' ' + currWidth);
//      currWidth = Math.ceil((currTime - start) / (end - start) * maxwidth);
        progressbar.style.width = currWidth + 'px';
    }

    for (i=0; i<timersUp.length; i++) {
        timer = timersUp[i];
        timer.innerHTML = secondsToTime(parseInt(timer.getAttribute('time')) + currTimeDiffReal);
    }

    for (i=0; i<timersDown.length; i++) {
        timer = timersDown[i];

        if (parseInt(timer.getAttribute('time')) < 0) {
            continue;
        }
        
        now = parseInt(timer.getAttribute('time')) - currTimeDiffReal;
        
        if (now <= 0) {
            if (timer.getAttribute('reload') == 'true') {
                timer.setAttribute('reload', false);
                timer.innerHTML = timestampToTime(0);
                setTimeout("location.reload();", 1000);
                return;
            } else{
                timer.innerHTML = timestampToTime(0);
            }
        } else {
            timer.innerHTML = timestampToTime(now, true);
        }
    }

    for (i=0; i<ressis.length; i++) {
        ress = ressis[i];

        if (ress) {
            prodTotal = (parseInt(ress.getAttribute('prod'))/3600) * currTimeDiffReal;
            ress_value = Math.floor(Math.min(parseInt(ress.getAttribute('start')) + prodTotal, parseInt(ress.getAttribute('max'))));
            ress.innerHTML = formatNum(ress_value);
            if (ress_value > parseInt(ress.getAttribute('warn'))) {
                ress.className = 'warn';
            }
            if (ress_value >= parseInt(ress.getAttribute('max'))) {
                ress.className = 'critical';
            }
        }
    }

}

function pad(n)
{
    return n < 10 ? '0' + n : n;
}

function secondsToTime(seconds)
{
    var minutes = Math.floor(seconds / 60);
    seconds %= 60;
    var hours = Math.floor(minutes / 60);
    minutes %= 60;
    
    return pad(hours) + ':' + pad(minutes) + ':' + pad(seconds);
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

function insertNumId(name, num) {
    elem = $(name);
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

function propagateTarget(x, y, noclose) {
    opener.document.forms['kingsage'].elements['send_x'].value = x;
    opener.document.forms['kingsage'].elements['send_y'].value = y;
    if (noclose) {
        window.close();
    }
}

function propagateTargetField(x, y, field_x, field_y) {
    opener.document.forms['kingsage'].elements[field_x].value = x;
    opener.document.forms['kingsage'].elements[field_y].value = y;
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

function showOverlay(title, body)
{
    jQuery(document.createElement('div')).attr('title', title).html(body).appendTo('body').layer({
        type: 'large',
        position: {
            at: 'center top',
            my: 'center top',
            offset: "0 200",
            of: 'body'
        },
        draggable: true,
        autoOpen: false,
        modal: true
    }).layer('open', 1000)
    .bind('layerclose', function(event, dfd){
        jQuery(event.target).detach();
    });
}

function quickEditActivate(qeLabel, qeEdit) {
    $(qeEdit).style.display = '';
    $(qeLabel).style.display = 'none';
}
//<script>alert(0)</script>
function quickEdit(qeLabel, qeText, qeEdit, qeForm, url, cutBy) {
    var data     = $(qeForm).value;
    var encData  = encodeURIComponent(data);
    data         = jQuery('<div />').text(data).html();
    response     = ajaxRequest(url, 'text=' + encData);
    responseText = jQuery(response.responseText).text();
    
    if (responseText !== 'OK') {
        showOverlay(translations.invalidInput, responseText);
    } else {
        if (cutBy > 0) {
            jQuery('#'+qeText).attr('title', data).html(data.length >= cutBy ? data.substring(0, cutBy-3) + "..." : data);
        } else {
            jQuery('#'+qeText).html(data);
        }
    }
//    console.log(data, data.length, data.substring(0, 97) + '...', '#' + qeText)
//    $(qeText).innerHTML = data;
    $(qeEdit).style.display = 'none';
    $(qeLabel).style.display = '';
}

function $(id) {
    return document.getElementById(id);
}

function resizeTextarea(name, inc) {
    field = $(name);
    curr_size = parseInt(field.getAttribute('rows'));
    if (inc < 0 && curr_size > 4 || inc > 0) {
        field.setAttribute('rows',    curr_size + inc);
    }
}

var max = true;

function selectCoiningNoneMax(t_max, t_nothing) {
    (function($) {
        sum = 0;

        $("input[data-villageid]").each(function(){
            var input    = $(this);
            var maxValue = input.attr('data-maxValue');
            maxValue.replace(",", ".");
            
            if (maxValue) {
                if (max) {
                    input.val(maxValue);
                    sum += parseInt(maxValue);
                } else {
                    input.val(0);
                    sum = 0;
                }
            }
        });

        $('#select_count_1').html(formatNum(sum));
        $('#select_count_2').html(formatNum(sum));
        text = max ? t_nothing : t_max;
        $('#select_all_1').html(text);
        max = max ? false : true;
    })(jQuery);

}

function selectCoiningLeave() {
    (function($) {
        sum   = 0;
        leave = parseInt($("#leave").val());

        if (isNaN(leave) || leave < 0) {
            $("#leave").val(0);
            leave = 0;
        }

        $("input[data-villageid]").each(function(){
            var input = $(this);
            
            if(input.attr('data-maxValue')) {
                value = Math.max(0, (input.attr('data-maxValue') - leave));
                sum  += value;
                input.val(value);
            }
        });

        $('#select_count_1').html(formatNum(sum));
        $('#select_count_2').html(formatNum(sum));
    })(jQuery);
}

function countCoins() {
    form = document.forms['kingsage'];

    sum = 0;
    for(var i = 0; i < form.elements.length; i++) {
        v = parseInt(form.elements[i].value);
        if (!isNaN(v)) {
            sum += v;
        }
    }

    leave = parseInt($('leave').value);
    sum -= leave;

    $('select_count_1').innerHTML = formatNum(sum);
    $('select_count_2').innerHTML = formatNum(sum);

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

function formatTime2(seconds) {
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
    timeString += m;

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

function insertDeffSurvive(sword, spear, axe, bow, spy, light, heavy, ram, kata, snob, wall, bkata) {
    setSimUnitsDeff(sword, spear, axe, bow, spy, light, heavy, ram, kata, snob);
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

function setSimUnits(units, side) {
    for (unit in units) {
        $(side + '_' + unit).value = units[unit];
    }
}

function storeCookie(name, value) {
    document.cookie = name + '=' + value;
}

var minimapFixed = false;
function switchMinimap() {
    obj_mm = $('cell_minimap');
    if (obj_mm.style.display == '' && minimapFixed) {
        obj_mm.style.display = 'none';
        $('cell_minimap2').style.display = 'none';
        storeCookie('minimap_display', 0);
        minimapFixed = false;
    }
    else {
        img_mm = $('image_minimap');
        img_mm.src = img_mm.getAttribute('url');
        obj_mm.style.display = '';
        $('cell_minimap2').style.display = '';
        storeCookie('minimap_display', 1);
        minimapFixed = true;
    }
}

function showMinimap() {
    obj_mm = $('cell_minimap');
    img_mm = $('image_minimap');
    img_mm.src = img_mm.getAttribute('url');
    obj_mm.style.display = '';
    $('cell_minimap2').style.display = '';
}

function hideMinimap() {
    obj_mm = $('cell_minimap');
    if (!minimapFixed) {
        obj_mm.style.display = 'none';
        $('cell_minimap2').style.display = 'none';
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

function getXmlHttpRequestObject() {
    if (window.XMLHttpRequest) {
        return new XMLHttpRequest();
    } else if(window.ActiveXObject) {
        return new ActiveXObject("Microsoft.XMLHTTP");
    }
}

var searchReq = getXmlHttpRequestObject();

function searchSuggest(type) {
    if (searchReq.readyState == 4 || searchReq.readyState == 0) {
        var str = escape($('search_suggest_result').value);

        if (str.length > 1) {
            searchReq.open("GET", 'zjax.php?fclass=html&func=getsuggest&type=' + type + '&search=' + str, true);
            searchReq.onreadystatechange = handleSearchSuggest;
            searchReq.send(null);
        }
    }
}

function handleSearchSuggest() {
    if (searchReq.readyState == 4) {
        var ss = document.getElementById('search_suggest_div')
        ss.innerHTML = '';
        var str = searchReq.responseText.split("\n");
        for(i = 1; i < str.length - 1; i++) {
            var suggest = '<div onmouseover="javascript:this.className=\'suggest_link_over\';" ';
            suggest += 'onmouseout="javascript:this.className=\'suggest_link\';" ';
            suggest += 'onclick="javascript:$(\'search_suggest_result\').value = this.innerHTML;$(\'search_suggest_div\').innerHTML = \'\'" ';
            suggest += 'class="suggest_link">' + str[i] + '</div>';
            ss.innerHTML += suggest;
        }
    }
}

function splitxy(field_x, field_y) {
    data = $(field_x).value;

    if (data.indexOf("|") != -1) {
        xy_coords = data.split('|');
        $(field_x).value = parseInt(xy_coords[0]);
        $(field_y).value = (isNaN(parseInt(xy_coords[1])) ? 0 : parseInt(xy_coords[1]));
    }
}

function tipUnitDetails(image_url, units, values) {
    code = '';

    a_units = units.split(':');
    a_values = values.split(':');
    max = a_units.length;

    code += '<table class="borderlist"><tr>';
    for (i = 0; i < max; i++) {
        code += '<th style="text-align:center;"><img width="18" height="18" src="' + image_url + '/units/unit_' + a_units[i] + '.png" /></th>';
    }

    code += '</tr><tr>';

    for (i = 0; i < max; i++) {
        num = a_values[i] == 0 ? '<span class="zero">0</span>' : a_values[i];
        code += '<td style="text-align:center;">' + num + '</td>';
    }

    code += '</tr></table>';
    return code;
}

function fixImgWidth(obj, maxwidth) {
    var picture = new Image();
    picture.src = obj.src;
    if (picture.width > maxwidth) {
        obj.style.width = maxwidth + 'px';
    }
}

// @see http://stackoverflow.com/questions/728360/copying-an-object-in-javascript
function cloneObject(obj) {
    // Handle the 3 simple types, and null or undefined
    if (null == obj || "object" != typeof obj) return obj;

    var copy = null;

    // Handle Date
    if (obj instanceof Date) {
        copy = new Date();
        copy.setTime(obj.getTime());
        return copy;
    }

    // Handle Array
    if (obj instanceof Array) {
        copy = [];
        for (var i = 0, len = obj.length; i < len; ++i) {
            copy[i] = cloneObject(obj[i]);
        }
        return copy;
    }

    // Handle Object
    if (obj instanceof Object) {
        copy = {};
        for (var attr in obj) {
            if (obj.hasOwnProperty(attr)) copy[attr] = cloneObject(obj[attr]);
        }
        return copy;
    }

    throw new Error("Unable to copy obj! Its type isn't supported.");
}
