function SGPBNewsletter()
{this.init();}
SGPBNewsletter.prototype.getTinymceContent=function()
{if(jQuery('.wp-editor-wrap').hasClass('tmce-active')){return tinyMCE.activeEditor.getContent();}
return jQuery('#sgpb-newsletter-text').val();};SGPBNewsletter.prototype.init=function()
{var sendButton=jQuery('.js-send-newsletter');if(!sendButton.length){return false;}
var that=this;sendButton.bind('click',function(e){e.preventDefault();var currentBtn=jQuery(this);var validationStatus=true;var testSendingStatus=currentBtn.data('send-type');var testSendingEmails=jQuery('.sgpb-newlsetter-test-emails').val();var fromEmail=jQuery('.sgpb-newsletter-from-email').val();var subscriptionFormId=jQuery('.js-sg-newsletter-forms option:selected').val();subscriptionFormId=parseInt(subscriptionFormId);var validateEmail=fromEmail.search(/^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,10})+$/);if(isNaN(subscriptionFormId)){jQuery('.sgpb-newsletter-popup-error').removeClass('sg-hide-element');validationStatus=false;}
if(validateEmail===-1){validationStatus=false;jQuery('.sgpb-newsletter-from-email-error').removeClass('sg-hide-element');return false;}
if(!validationStatus){return false;}
jQuery('.sgpb-newsletter-validation').addClass('sg-hide-element');var emailsInFlow=jQuery('.sgpb-emails-in-flow').val();emailsInFlow=parseInt(emailsInFlow);var newsletterSubject=jQuery('.sgpb-newsletter-subject').val();var messageBody=that.getTinymceContent();var data={nonce:SGPB_JS_PARAMS.nonce,action:'sgpb_send_newsletter',newsletterData:{subscriptionFormId:subscriptionFormId,beforeSend:function(){currentBtn.next('.sgpb-js-newsletter-spinner').removeClass('sgpb-hide');jQuery('.sgpb-newsletter-notice').addClass('sgpb-hide');jQuery('.sgpb-newsletter-success-message').addClass('sgpb-hide');jQuery('.sgpb-newsletter-test-success-message').addClass('sgpb-hide');},fromEmail:fromEmail,emailsInFlow:emailsInFlow,newsletterSubject:newsletterSubject,messageBody:messageBody,testSendingStatus:testSendingStatus,testSendingEmails:testSendingEmails}};jQuery.post(ajaxurl,data,function(response){if(response==='test'){jQuery('.sgpb-newsletter-test-success-message').removeClass('sgpb-hide');}
else{jQuery('.sgpb-newsletter-success-message').removeClass('sgpb-hide');}
jQuery('.sgpb-newsletter-notice').removeClass('sgpb-hide');jQuery('.sgpb-js-newsletter-spinner').addClass('sgpb-hide');});});};jQuery(document).ready(function(){new SGPBNewsletter();});