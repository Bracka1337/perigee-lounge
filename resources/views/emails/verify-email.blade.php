<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="x-apple-disable-message-reformatting">
    <meta name="color-scheme" content="light">
    <meta name="supported-color-schemes" content="light">
    <title>{{ $appName }}</title>
    <!--[if mso]>
    <noscript>
        <xml>
            <o:OfficeDocumentSettings>
                <o:PixelsPerInch>96</o:PixelsPerInch>
            </o:OfficeDocumentSettings>
        </xml>
    </noscript>
    <![endif]-->
    <style>
        :root { color-scheme: light; }
        div[style*="margin: 16px 0"] { margin: 0 !important; }
    </style>
</head>
<body style="margin: 0; padding: 0; -webkit-text-size-adjust: 100%; -ms-text-size-adjust: 100%; background-color: #e8e8e8; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;" bgcolor="#e8e8e8">
    
    <!-- Preheader -->
    <div style="display:none; font-size:1px; color:#e8e8e8; line-height:1px; max-height:0px; max-width:0px; opacity:0; overflow:hidden;">
        {{ __('verify-email-email-content') }}
    </div>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" bgcolor="#e8e8e8" style="background-color: #e8e8e8;">
        <tr>
            <td align="center" valign="top" bgcolor="#e8e8e8" style="padding: 40px 16px; background-color: #e8e8e8;">
                
                <table role="presentation" width="520" cellpadding="0" cellspacing="0" border="0" style="max-width: 520px; width: 100%;">
                    
                    <!-- Stars top -->
                    <tr>
                        <td align="center" style="padding-bottom: 20px;">
                            <span style="font-size: 10px; color: #999999;">·</span>
                            <span style="font-size: 10px; color: #888888;">&nbsp;&nbsp;·&nbsp;&nbsp;</span>
                            <span style="font-size: 12px; color: #666666;">✦</span>
                            <span style="font-size: 10px; color: #888888;">&nbsp;&nbsp;·&nbsp;&nbsp;</span>
                            <span style="font-size: 10px; color: #999999;">·</span>
                        </td>
                    </tr>
                    
                    <!-- Card -->
                    <tr>
                        <td bgcolor="#ffffff" style="background-color: #ffffff; border: 1px solid #d0d0d0; border-radius: 16px;">
                            
                            <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                
                                <!-- Logo -->
                                <tr>
                                    <td align="center" style="padding: 32px 32px 20px 32px;">
                                        <img src="{{ config('app.url') }}/images/logo.png" 
                                             alt="{{ $appName }}" 
                                             width="110" 
                                             style="display: block; width: 110px; height: auto; border: 0;">
                                    </td>
                                </tr>
                                
                                <!-- Divider -->
                                <tr>
                                    <td style="padding: 0 32px;">
                                        <div style="height: 1px; background-color: #e0e0e0; font-size: 1px; line-height: 1px;">&nbsp;</div>
                                    </td>
                                </tr>
                                
                                <!-- Content -->
                                <tr>
                                    <td style="padding: 28px 32px 20px 32px;">
                                        <h1 style="margin: 0 0 14px 0; font-size: 24px; font-weight: 600; color: #1a1a1a; text-align: center; line-height: 1.3;">
                                            {{ __('verify-email-email-title') }}
                                        </h1>
                                        <p style="margin: 0; font-size: 15px; line-height: 1.6; color: #555555; text-align: center;">
                                            {{ __('verify-email-email-content') }}
                                        </p>
                                    </td>
                                </tr>
                                
                                <!-- Button -->
                                <tr>
                                    <td align="center" style="padding: 8px 32px 28px 32px;">
                                        <table role="presentation" cellpadding="0" cellspacing="0" border="0">
                                            <tr>
                                                <td align="center" bgcolor="#1a1a1a" style="background-color: #1a1a1a; border-radius: 8px;">
                                                    <!--[if mso]>
                                                    <v:roundrect xmlns:v="urn:schemas-microsoft-com:vml" xmlns:w="urn:schemas-microsoft-com:office:word" href="{{ $url }}" style="height:48px;v-text-anchor:middle;width:200px;" arcsize="17%" strokecolor="#1a1a1a" fillcolor="#1a1a1a">
                                                    <w:anchorlock/>
                                                    <center style="color:#ffffff;font-family:sans-serif;font-size:14px;font-weight:bold;">{{ __('verify-email-email-button') }}</center>
                                                    </v:roundrect>
                                                    <![endif]-->
                                                    <!--[if !mso]><!-->
                                                    <a href="{{ $url }}" target="_blank"
                                                       style="display: inline-block; padding: 14px 32px;
                                                              color: #ffffff; text-decoration: none;
                                                              font-weight: 600; font-size: 14px; letter-spacing: 0.3px;
                                                              text-transform: uppercase; line-height: 1.2;">
                                                        {{ __('verify-email-email-button') }}
                                                    </a>
                                                    <!--<![endif]-->
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                
                                <!-- Outro -->
                                <tr>
                                    <td style="padding: 0 32px 24px 32px;">
                                        <p style="margin: 0; font-size: 13px; line-height: 1.5; color: #888888; text-align: center;">
                                            {{ __('verify-email-email-not-requested') }}
                                        </p>
                                    </td>
                                </tr>
                                
                                <!-- Divider -->
                                <tr>
                                    <td style="padding: 0 32px;">
                                        <div style="height: 1px; background-color: #e0e0e0; font-size: 1px; line-height: 1px;">&nbsp;</div>
                                    </td>
                                </tr>
                                
                                <!-- Fallback -->
                                <tr>
                                    <td style="padding: 20px 32px;">
                                        <p style="margin: 0 0 10px 0; font-size: 11px; line-height: 1.5; color: #888888; text-align: center;">
                                            {{ __('verify-email-email-fallback') }}
                                        </p>
                                        <p style="margin: 0; font-size: 10px; line-height: 1.5; word-break: break-all; text-align: center;">
                                            <a href="{{ $url }}" style="color: #666666; text-decoration: underline;">{{ $url }}</a>
                                        </p>
                                    </td>
                                </tr>
                                
                                <!-- Footer -->
                                <tr>
                                    <td bgcolor="#f5f5f5" style="background-color: #f5f5f5; padding: 20px 32px; border-radius: 0 0 15px 15px;">
                                        <p style="margin: 0 0 4px 0; font-size: 12px; color: #666666; text-align: center;">
                                            {{ __('email-regards') }},
                                            <span style="color: #1a1a1a; font-weight: 500;">{{ $appName }}</span>
                                        </p>
                                        <p style="margin: 0; font-size: 10px; color: #999999; text-align: center;">
                                            © {{ date('Y') }} {{ $appName }}
                                        </p>
                                    </td>
                                </tr>
                                
                            </table>
                        </td>
                    </tr>
                    
                    <!-- Stars bottom -->
                    <tr>
                        <td align="center" style="padding-top: 20px;">
                            <span style="font-size: 10px; color: #aaaaaa;">·</span>
                            <span style="font-size: 10px; color: #999999;">&nbsp;&nbsp;·&nbsp;&nbsp;</span>
                            <span style="font-size: 10px; color: #aaaaaa;">·</span>
                        </td>
                    </tr>
                    
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
