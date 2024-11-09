/*
 * jQuery NavDropDown v1.0.0 
 *
 * Copyright (c) 2008 Taranets Aleksey
 * www: markup-javascript.com
 * Licensed under the MIT License:
 * http://www.opensource.org/licenses/mit-license.php
 */

jQuery.fn.NavDropDown=function(j){var j=jQuery.extend({duration:300,hoverElement:'li',dropHolder:'div',hoverClass:'hover',showEffect:'slide'},j);return this.each(function(){var f=jQuery(this);var g=jQuery(j.hoverElement+':has('+j.dropHolder+')',f);var h=j.duration;g.each(function(i,a){a=jQuery(a);var b=$(j.dropHolder+' :first',a);var c=$(j.dropHolder,a);a.h=b.outerHeight();var d={};var e={};if(j.showEffect=='slide'){c.css({'height':0,'overflow':'hidden'});b.css({'marginTop':-a.h,'overflow':'hidden'});d.SE={height:a.h};d.ME={marginTop:0};e.SE={height:0};e.ME={marginTop:-a.h}}if(j.showEffect=='fade'){c.css({'opacity':0});d.SE={opacity:1};e.SE={opacity:0};d.ME={};e.ME={}}if(j.showEffect=='slide&fade'){c.css({'height':0,'overflow':'hidden','opacity':0});b.css({'marginTop':-a.h,'overflow':'hidden'});d.SE={height:a.h,opacity:1};d.ME={marginTop:0};e.SE={height:0,opacity:0};e.ME={marginTop:-a.h}}a.hoverEl=false;a.hover(function(){if(this.timer)clearTimeout(this.timer);a.hoverEl=true;$(this).addClass(j.hoverClass);c.animate(d.SE,{queue:false,duration:h});b.animate(d.ME,{queue:false,duration:h})},function(){this.timer=setTimeout(function(){a.hoverEl=false;b.animate(e.ME,{queue:false,duration:h});c.animate(e.SE,{queue:false,duration:h,complete:function(){if(!a.hoverEl)$(a).removeClass(j.hoverClass)}})},100)})})})};

