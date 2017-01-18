function KAMap (image_url, snob_max_distance)
{
    this.options = {
        units: new Array(
            'farmer',
            'sword',
            'spear',
            'axe',
            'bow',
            'spy',
            'spy-slow',
            'light',
            'heavy',
            'ram',
            'kata',
            'snob'
        )
    }
        
    this.options.image_url         = image_url;
    this.options.snob_max_distance = snob_max_distance;
}

KAMap.prototype.tooltipDetails = function(
    village_id, 
    village_name, 
    points, 
    owner, 
    ally, 
    moral, 
    groups, 
    runtimes, 
    ressis, 
    settlers, 
    mules, 
    units, 
    isProtectedFromAttacks, 
    isProtectedFromEnnoblings, 
    isRankingBattle
) {
    var showUnits = (units || runtimes);
    var i         = 0;
    var unitCount = this.options.units.length;

    if (showUnits) {
        showUnits = false;

        for (i = 0; i < unitCount; i++) {
            unit = this.options.units[i];

            if (i < unitCount && !units && typeof(speed[unit]) == 'undefined') {
                continue;
            }

            showUnits = true;
            break;
        }
    }

    showMules = (mules || runtimes && typeof(speed['donkey']) != 'undefined');
    showBoth  = (showUnits && showMules);

    code  = '';
    code += '<table class="borderlist">';
    code += '<tr><th colspan="2">';

    if (isRankingBattle) {
        code += '<img width="18" height="18" alt="' + lang['RANKING_BATTLE'] + '" title="' + lang['RANKING_BATTLE'] + '" src="/img/premium/honorable-enemy_icon.png" style="position: relative; top: 2px;" />&nbsp;';
    }

    code += village_name;

    if (isProtectedFromAttacks) {
        code += '<img src="' + this.options.image_url + '/premium/relics/icons/buffs-viewer/peace-treaty-newbie-phase1.png">';
    }

    if (isProtectedFromEnnoblings) {
        code += '<img src="' + this.options.image_url + '/premium/relics/icons/buffs-viewer/peace-treaty-newbie-phase2.png">';
    }

    code += '</th></tr>';
    code += '<tr><td>' + lang['POINTS'] + ':</td><td>' + points + '</td></tr>';
    
    if (owner != '' && owner != 0) {
        code += '<tr><td>' + lang['OWNER'] + ':</td><td>' + owner + '</td></tr>';
    } else {
        code += '<tr><td colspan="2">' + lang['LEFT'] + '</td></tr>';
    }
    
    if (ally) {
        code += '<tr><td>' + lang['ALLY'] + ':</td><td>' + ally + '</td></tr>';
    }
    
    if (moral) {
        code += '<tr><td>' + lang['MORAL'] + ':</td><td>' + moral + '</td></tr>';
    }
    
    if (groups) {
        code += '<tr><td>' + lang['GROUPS'] + ':</td><td>' + groups + '</td></tr>';
    }
    
    if (ressis) {
        ress  = ressis.split(':');
        code += '<tr><td colspan="2">';
        code += '<table class="noborder"><tr>';
        code += '<td><img src="' + this.options.image_url + '/res2.png" /></td><td>' + ress[1] + '</td>';
        code += '<td><img src="' + this.options.image_url + '/res1.png" /></td><td>' + ress[0] + '</td>';
        code += '<td><img src="' + this.options.image_url + '/res3.png" /></td><td>' + ress[2] + '</td>';
        code += '<td><img src="' + this.options.image_url + '/storage.png" /></td><td>' + ress[3] + '</td>';
        code += '</tr></table>';
        code += '</td></tr>';
    }

    if (settlers) {
        code += '<tr><td colspan="2">';
        code += '<table class="noborder"><tr>';

        if (settlers) {
            code += '<td><img src="' + this.options.image_url + '/worker.png" /></td><td>' + settlers + '</td>';
        }

        code += '</tr></table>';
        code += '</td></tr>';
    }

    if (showUnits || showMules) {
        code += '<tr>';
        code += '<td colspan="2">';
        code += '<table class="noborder">';
        code += '<tr>';
    }

    if (showUnits) {
        code += '<td>';
        code += '<table class="noborder">';
        code += '<tr>';

        for (i = 0; i < unitCount; i++) {
            unit = this.options.units[i];

            if (i < unitCount && !units && typeof(speed[unit]) == 'undefined') {
                continue;
            }

            unit  = this.options.units[i];
            code += '<td style="text-align:center;"><img src="' + this.options.image_url + '/units/unit_' + unit + '.png" width="18" /></td>';
        }
        
        code += '</tr>';

        if (units) {
            village_units = units.split(':');
            code         += '<tr>';
            
            for (i = 0; i < unitCount; i++) {
                unit = this.options.units[i];

                if (i < unitCount && !units && typeof(speed[unit]) == 'undefined') {
                    continue;
                }

                if (units) {
                    num = village_units[i];
                } else {
                    num = -1;
                }

                if (num == -1) {
                    code += '<td>&nbsp;</td>';
                } else {
                    num = (num == 0 ? '<span class="zero">0</span>' : num);
                    code += '<td style="text-align:center;">' + num + '</td>';
                }
            }
            code += '</tr>';
        }

        if (runtimes) {
            code += '<tr>';
            xy    = runtimes.split('.');
            dist  = Math.sqrt(Math.pow(xy[0] - xy[2], 2) + Math.pow(xy[1] - xy[3], 2));
            
            for (i = 0; i < unitCount; i++) {
                unit = this.options.units[i];
                
                if (i < unitCount && !units && typeof(speed[unit]) == 'undefined') {
                    continue;
                }

                if (typeof(speed[unit]) == 'undefined') {
                    code += '<td class="text_info">&nbsp;</td>';
                } else {
                    style = 'text-align:center;';
                    
                    if (unit == 'snob' && dist > this.options.snob_max_distance) {
                        style = ' color:red;';
                    }
                    
                    code += '<td class="text_info" style="' + style + '">' + formatTime2(Math.round(dist * speed[unit])) + '</td>';
                }
            }
            
            code += '</tr>';
        }

        code += '</table>';
        code += '</td>';
    }

    if (showMules) {
        code += '<td' + (showUnits ? ' style="border-left: 1px solid #CFAB65"': '') + '>';
        code += '<table class="noborder">';
        code += '<tr>';
        code += '<td style="text-align:center;"><img src="' + this.options.image_url + '/units/unit_donkey.png" width="18" /></td>';
        code += '</tr>';

        if (mules) {
            code += '<tr>';

            if (!mules) {
                code += '<td>&nbsp;</td>';
            } else {
                num = (mules == 0 ? '<span class="zero">0</span>' : mules);
                code += '<td style="text-align:center;">' + mules + '</td>';
            }

            code += '</tr>';
        }

        if (runtimes) {
            code += '<tr>';
            xy    = runtimes.split('.');
            dist  = Math.sqrt(Math.pow(xy[0] - xy[2], 2) + Math.pow(xy[1] - xy[3], 2));

            if (typeof(speed['donkey']) == 'undefined') {
                code += '<td class="text_info">&nbsp;</td>';
            } else {
                code += '<td style="text-align:center;" class="text_info">' + formatTime2(Math.round(dist * speed['donkey'])) + '</td>';
            }

            code += '</tr>';
        }

        code += '</table>';
        code += '</td>';
    }

    if (showUnits || showMules) {
        code += '</tr>';
        code += '</table>';
        code += '</td>';
        code += '</tr>';
    }

    code += '</table>';
    
    return code;
}

function MiniMap (x, y, size_x, size_y, width, height, village_id, rtl_diff)
{
    this.options = {
        x:          0,
        y:          0,
        size_x:     0,
        size_y:     0,
        width:      0,
        height:     0,
        village_id: 0,
        av:         0,
        xs:         0,
        ys:         0
    }

    this.options.x          = x; // Aktueller Mittelpunkt
    this.options.y          = y; // Aktueller Mittelpunkt
    this.options.size_x     = size_x; // Anzahl der sichtbaren Felder vom Mittelpunkt aus
    this.options.size_y     = size_y; // Anzahl der sichtbaren Felder vom Mittelpunkt aus
    this.options.width      = width; // Feldbreite
    this.options.height     = height; // FeldhÃ¶he
    this.options.village_id = village_id; // Aktuelles Dorf
    this.options.rtl_diff   = rtl_diff;

}

MiniMap.prototype.jumpClick = function(event)
{
    if (!event) {
        event = window.event;
    }
    
    o = $('minimap_jumpclick');

    var mouse_pos = {
        left: event.clientX,
        top:  event.clientY
    };
    
    var body = (window.document.compatMode && window.document.compatMode == "CSS1Compat") ? window.document.documentElement : window.document.body || null;

    if (body.scrollTop > 0) {
        mouse_pos.left += body.scrollLeft;
        mouse_pos.top  += body.scrollTop;
    } else if (document.body.scrollTop > 0) {
        mouse_pos.left += document.body.scrollLeft;
        mouse_pos.top  += document.body.scrollTop;
    }
    
    var img_pos = {
        top:  0,
        left: 0
    };

    if (!o) {
        return;
    }
    
    if (typeof o != 'object'  || typeof o.offsetTop == 'undefined') {
        return;
    }
    
    while (o && o.tagName != 'BODY') {
        img_pos.top  += parseInt( o.offsetTop );
        img_pos.left += parseInt( o.offsetLeft );
        o             = o.offsetParent;
    }

    real_x = mouse_pos.left - img_pos.left;
    real_y = mouse_pos.top  - img_pos.top;

    if (this.options.rtl_diff) {
        real_x = this.options.rtl_diff - real_x;
    }

    new_x = this.options.xs + Math.floor(real_x / this.options.width);
    new_y = this.options.ys + Math.floor(real_y / this.options.height);

    av_code  = (this.options.av != 0) ? '&av=' + this.options.av : '';
    url      = 'zjax.php?func=getMapData&fclass=game';
    response = ajaxRequest(url, 'x=' + new_x + '&y=' + new_y + av_code);
    
    jQuery('div#mapContainer').html(response.responseText);

    if (jQuery('img#image_minimap').attr('src') == jQuery('img#image_minimap').attr('url')) {
        jQuery('img#image_minimap').attr('src', 'minimap.php?x=' + new_x + '&y=' + new_y + av_code);
    }

    jQuery('img#image_minimap').attr('url', 'minimap.php?x=' + new_x + '&y=' + new_y + av_code);
    jQuery('input[name=x]').attr('value', new_x);
    jQuery('input[name=y]').attr('value', new_y);

    this.options.x  = updatedMinimap.options.x;
    this.options.y  = updatedMinimap.options.y;
    this.options.xs = updatedMinimap.options.xs;
    this.options.ys = updatedMinimap.options.ys;
}

function MapGoLink (village_id,x,y,player_id)
{
    av_code  = (player_id != 0) ? '&av=' + player_id : '';
    url      = 'zjax.php?func=getMapData&fclass=game';
    response = ajaxRequest(url, 'x=' + x + '&y=' + y + av_code);
    
    jQuery('div#mapContainer').html(response.responseText);

    if (jQuery('img#image_minimap').attr('src') == jQuery('img#image_minimap').attr('url')) {
        jQuery('img#image_minimap').attr('src', 'minimap.php?x=' + x + '&y=' + y + av_code);
    }

    jQuery('img#image_minimap').attr('url', 'minimap.php?x=' + x + '&y=' + y + av_code);
    jQuery('input[name=x]').attr('value', x);
    jQuery('input[name=y]').attr('value', y);

    obj_minimap.options.x  = updatedMinimap.options.x;
    obj_minimap.options.y  = updatedMinimap.options.y;
    obj_minimap.options.xs = updatedMinimap.options.xs;
    obj_minimap.options.ys = updatedMinimap.options.ys;
}