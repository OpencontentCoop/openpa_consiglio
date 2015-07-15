<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
    <title>{if is_set($title)}{$title}{/if}</title>
    <style type="text/css">
        {literal}
        body{font-family:"Lucida Grande", "Helvetica Neue", Helvetica, Arial, sans-serif;}
        #outlook a {padding:0;}
        body{width:100% !important; -webkit-text-size-adjust:100%; -ms-text-size-adjust:100%; margin:0; padding:0; background: #ccc;}
        .ExternalClass {width:100%;}
        .ExternalClass, .ExternalClass p, .ExternalClass span, .ExternalClass font, .ExternalClass td, .ExternalClass div {line-height: 100%;}
        #backgroundTable {margin:0 auto; padding:0; width:90% !important; line-height: 100% !important;}
        img {outline:none; text-decoration:none; -ms-interpolation-mode: bicubic;}
        a img {border:none;}
        .image_fix {display:block;}
        p {font-size: 15px; line-height: 150%; margin: 0;}
        h1, h2, h3, h4, h5, h6 {color: black !important;}
        h1 a, h2 a, h3 a, h4 a, h5 a, h6 a {color: blue !important;}
        h1 a:active, h2 a:active,  h3 a:active, h4 a:active, h5 a:active, h6 a:active {
            color: red !important; /* Preferably not the same color as the normal header link color.  There is limited support for psuedo classes in email clients, this was added just for good measure. */
        }
        h1 a:visited, h2 a:visited,  h3 a:visited, h4 a:visited, h5 a:visited, h6 a:visited {
            color: purple !important; /* Preferably not the same color as the normal header link color. There is limited support for psuedo classes in email clients, this was added just for good measure. */
        }
        table td {border-collapse: collapse;}
        table { border-collapse:collapse; mso-table-lspace:0pt; mso-table-rspace:0pt; }
        h2 {font-size: 20px}
        a {color: #1a7fb8;}
        hr {border: 0; height: 1px;	background: #ccc;}
        @media only screen and (max-device-width: 480px) {
            a[href^="tel"], a[href^="sms"] {
                text-decoration: none;
                color: black; /* or whatever your want */
                pointer-events: none;
                cursor: default;
            }
            .mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
                text-decoration: default;
                color: red !important; /* or whatever your want */
                pointer-events: auto;
                cursor: default;
            }
        }
        @media only screen and (min-device-width: 768px) and (max-device-width: 1024px) {
            a[href^="tel"], a[href^="sms"] {
                text-decoration: none;
                color: blue; /* or whatever your want */
                pointer-events: none;
                cursor: default;
            }
            .mobile_link a[href^="tel"], .mobile_link a[href^="sms"] {
                text-decoration: default;
                color: red !important;
                pointer-events: auto;
                cursor: default;
            }
        }
        @media only screen and (-webkit-min-device-pixel-ratio: 2) {
            /* Put your iPhone 4g styles in here */
        }
        @media only screen and (-webkit-device-pixel-ratio:.75){
            /* Put CSS for low density (ldpi) Android layouts in here */
        }
        @media only screen and (-webkit-device-pixel-ratio:1){
            /* Put CSS for medium density (mdpi) Android layouts in here */
        }
        @media only screen and (-webkit-device-pixel-ratio:1.5){
            /* Put CSS for high density (hdpi) Android layouts in here */
        }
        {/literal}
    </style>

    <!--[if IEMobile 7]>
    <style type="text/css">

    </style>
    <![endif]-->

    <!--[if gte mso 9]>
    <style>
        /* Target Outlook 2007 and 2010 */
    </style>
    <![endif]-->
</head>
<body style="background: #ccc;" bgcolor="#ccc">
<table align='center' bgcolor='#fff' border='0' cellpadding='0' cellspacing='0' id='backgroundTable' style='background: #fff;'>
    <tr>
        <td colspan="2" style="text-align: center; padding: 20px 20px 0;">
            <a href="http://{ezini("SiteSettings","SiteURL")}" style="cursor: default">
                Inserire logo
            </a>
        </td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: center; padding: 20px;"><hr /></td>
    </tr>
    <tr>
        <td colspan="2" style="text-align: left; padding: 0 10%;">
            <p><i>Messaggio generato automaticamente, si prega di non rispondere.</i></p>
            {$content}
        </td>
    </tr>
</table>
{*
<table align='center' bgcolor='#fff' border='0' cellpadding='0' cellspacing='0' style='background: #fff;' width='100%'>
<tr>
  <td>
    <table align='center'border='0' cellpadding='0' cellspacing='0' width='600px'>
      <tbody>
        <tr>
          <td height="20" colspan="6"></td>
        </tr>
        <tr>
          <td width="20"></td>
          <td width="121"></td>
          <td align="center">
            <a href="{$pagedata.contacts.facebook}">
              <img src="http://{ezini("SiteSettings","SiteURL")}{'facebook.png'|ezimage(no)}" alt="Facebook">
            </a>
          </td>
          <td align="center">
            <a href="{$pagedata.contacts.twitter}">
              <img src="http://{ezini("SiteSettings","SiteURL")}{'twitter.png'|ezimage(no)}" alt="Twitter">
            </a>
          </td>
          <td width="121"></td>
          <td width="20"></td>
      </tbody>
    </table>
    <table align='center'border='0' cellpadding='20' cellspacing='0' width='600px'>
      <tr>
        <td style="text-align: center;">
          <p>
            &copy; {currentdate()|datetime('custom', '%Y')} <a href="http://{ezini("SiteSettings","SiteURL")}">{ezini("SiteSettings","SiteName")}</a>
          </p>
          <p><small>
            {foreach $pagedata.contacts as $index => $value}
              {if array('facebook','twitter')|contains($index)}{skip}{/if}
              <strong>{$index}:</strong> {$value}
              {delimiter}&middot;{/delimiter}
            {/foreach}
          </small></p>
        </td>
      </tr>
    </table>
  </td>
</tr>
</table>
  *}
</body>
</html>
