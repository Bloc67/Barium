/* Any changes to this file will be overwritten. To change the content
of this file, edit the source files from which it was compiled. */
function getClosest(el,divID)
{if(typeof divID=='undefined'||divID==!1)
return null;do{if(el.nodeName==='TEXTAREA'||el.nodeName==='INPUT'||el.id==='error_box')
break;if(el.id===divID)
{return el}}
while(el=el.parentNode);return null}
function getSelectedText(divID)
{if(typeof divID=='undefined'||divID==!1)
return!1;var text='',selection,found=0,container=document.createElement("div");if(window.getSelection)
{selection=window.getSelection();text=selection.toString()}
else if(document.selection&&document.selection.type!='Control')
{selection=document.selection.createRange();text=selection.text}
for(var i=0;i<selection.rangeCount;i++){s=getClosest(selection.getRangeAt(i).startContainer,divID);e=getClosest(selection.getRangeAt(i).endContainer,divID);if(s!==null&&e!==null)
{found=1;container.appendChild(selection.getRangeAt(i).cloneContents());text=container.innerHTML;break}}
return found===1?text:!1}
function quotedTextClick(oOptions)
{text='';$('#quoteSelected_'+oOptions.msgID).hide();$.ajax({url:smf_prepareScriptUrl(smf_scripturl)+'action=quotefast;quote='+oOptions.msgID+';xml;pb='+oEditorID+';mode='+(oEditorObject.bRichTextEnabled?1:0),type:'GET',headers:{"X-SMF-AJAX":1},xhrFields:{withCredentials:typeof allow_xhjr_credentials!=="undefined"?allow_xhjr_credentials:!1},dataType:'xml',beforeSend:function(){ajax_indicator(!0)},complete:function(jqXHR,textStatus){ajax_indicator(!1)},success:function(data,textStatus,xhr){oOptions.text=oOptions.text.replaceAll(/<img src=".*?" alt="(.*?)" title=".*?" class="smiley">/,'$1');text=$(data).find('quote').text();text=text.match(/^\[quote(.*)]/ig)+oOptions.text+'[/quote]'+'\n\n';var e=$('#'+oEditorID).get(0);var oEditor=sceditor.instance(e);text=oEditor.toBBCode(text);oEditor.insert(text);if(typeof oJumpAnchor!='undefined'){if(navigator.appName=='Microsoft Internet Explorer')
window.location.hash=oJumpAnchor;else window.location.hash='#'+oJumpAnchor}},error:function(xhr,textStatus,errorThrown){}})}
$(function(){$(document).on('mouseup','.inner, .list_posts',function(){var oSelected={divID:$(this).attr('id'),msgID:$(this).data('msgid'),};oSelected.text=getSelectedText(oSelected.divID);if(typeof oSelected.text=='undefined'||oSelected.text==!1)
return!0;$('#quoteSelected_'+oSelected.msgID).show();$(document).off('click','#quoteSelected_'+oSelected.msgID+' a');$(document).one('click','#quoteSelected_'+oSelected.msgID+' a',function(e){e.preventDefault();quotedTextClick(oSelected)});$(document).on('click.ondeselecttext'+oSelected.msgID,function(){setTimeout(function(){selectedText=getSelectedText(oSelected.divID);if(typeof selectedText!='undefined'&&selectedText!=!1)
return;$(document).off('click','#quoteSelected_'+oSelected.msgID+' a');$('#quoteSelected_'+oSelected.msgID).hide();$(document).off('click.ondeselecttext'+oSelected.msgID)},1)});return!0})})