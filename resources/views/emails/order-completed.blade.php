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
        {{ __('order-email-subtitle') }}
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
                                
                                <!-- Title -->
                                <tr>
                                    <td style="padding: 28px 32px 16px 32px;">
                                        <h1 style="margin: 0 0 10px 0; font-size: 24px; font-weight: 600; color: #1a1a1a; text-align: center;">
                                            {{ __('order-email-title') }}
                                        </h1>
                                        <p style="margin: 0; font-size: 14px; line-height: 1.6; color: #555555; text-align: center;">
                                            {{ __('order-email-subtitle') }}
                                        </p>
                                    </td>
                                </tr>
                                
                                <!-- Order Info Box -->
                                <tr>
                                    <td style="padding: 0 32px 16px 32px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" 
                                               bgcolor="#f5f5f5" style="background-color: #f5f5f5; border-radius: 12px;">
                                            <tr>
                                                <td style="padding: 16px 20px;">
                                                    <div style="font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase; color: #888888; margin-bottom: 12px;">
                                                        {{ __('order-email-details') }}
                                                    </div>
                                                    
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="padding: 4px 0; font-size: 13px; color: #666666; width: 100px;">{{ __('order-email-number') }}:</td>
                                                            <td style="padding: 4px 0; font-size: 13px; color: #1a1a1a;">#{{ $order->id }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding: 4px 0; font-size: 13px; color: #666666;">{{ __('order-email-date') }}:</td>
                                                            <td style="padding: 4px 0; font-size: 13px; color: #1a1a1a;">{{ optional($order->completed_at)->format('Y-m-d H:i') }}</td>
                                                        </tr>
                                                        <tr>
                                                            <td style="padding: 4px 0; font-size: 13px; color: #666666;">{{ __('order-email-table') }}:</td>
                                                            <td style="padding: 4px 0; font-size: 13px; color: #1a1a1a;">{{ $order->table->name ?? '#'.$order->table_id }}</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                
                                <!-- Items -->
                                @if($order->items->isNotEmpty())
                                <tr>
                                    <td style="padding: 0 32px 16px 32px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" 
                                               bgcolor="#f5f5f5" style="background-color: #f5f5f5; border-radius: 12px;">
                                            <tr>
                                                <td style="padding: 16px 20px;">
                                                    <div style="font-size: 10px; letter-spacing: 1.5px; text-transform: uppercase; color: #888888; margin-bottom: 12px;">
                                                        {{ __('order-email-items') }}
                                                    </div>
                                                    
                                                    @foreach($order->items as $item)
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" style="margin-bottom: 8px; @if(!$loop->last) padding-bottom: 8px; border-bottom: 1px solid #e0e0e0; @endif">
                                                        <tr>
                                                            <td style="font-size: 13px; color: #1a1a1a; padding: 2px 0;">
                                                                {{ $item->menuItem->name[app()->getLocale()] ?? $item->menuItem->name['ru'] ?? '' }}
                                                            </td>
                                                            <td style="font-size: 13px; color: #1a1a1a; text-align: right; padding: 2px 0; white-space: nowrap;">
                                                                {{ number_format($item->total_price, 2, '.', ' ') }} €
                                                            </td>
                                                        </tr>
                                                        <tr>
                                                            <td colspan="2" style="font-size: 11px; color: #888888; padding: 2px 0;">
                                                                {{ number_format($item->unit_price, 2, '.', ' ') }} € × {{ $item->quantity }}
                                                            </td>
                                                        </tr>
                                                    </table>
                                                    @endforeach
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                @endif
                                
                                <!-- Totals -->
                                <tr>
                                    <td style="padding: 0 32px 20px 32px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" 
                                               bgcolor="#f5f5f5" style="background-color: #f5f5f5; border-radius: 12px;">
                                            <tr>
                                                <td style="padding: 16px 20px;">
                                                    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0">
                                                        <tr>
                                                            <td style="font-size: 13px; color: #666666; padding: 4px 0;">{{ __('order-email-subtotal') }}:</td>
                                                            <td style="font-size: 13px; color: #1a1a1a; text-align: right; padding: 4px 0;">{{ number_format($order->subtotal ?? 0, 2, '.', ' ') }} €</td>
                                                        </tr>
                                                        
                                                        @if($order->discount_total > 0)
                                                        <tr>
                                                            <td style="font-size: 13px; color: #dc2626; padding: 4px 0;">{{ __('order-email-discount') }}:</td>
                                                            <td style="font-size: 13px; color: #dc2626; text-align: right; padding: 4px 0;">-{{ number_format($order->discount_total, 2, '.', ' ') }} €</td>
                                                        </tr>
                                                        @endif
                                                        
                                                        <tr>
                                                            <td colspan="2" style="padding-top: 8px;">
                                                                <div style="height: 1px; background-color: #e0e0e0;"></div>
                                                            </td>
                                                        </tr>
                                                        
                                                        <tr>
                                                            <td style="font-size: 15px; font-weight: 600; color: #1a1a1a; padding: 8px 0 0 0;">{{ __('order-email-total') }}:</td>
                                                            <td style="font-size: 15px; font-weight: 600; color: #1a1a1a; text-align: right; padding: 8px 0 0 0;">{{ number_format($order->total, 2, '.', ' ') }} €</td>
                                                        </tr>
                                                    </table>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                
                                <!-- Loyalty Points -->
                                @php
                                    $loyaltyEarned = \App\Models\LoyaltyEarning::where('order_id', $order->id)
                                        ->where('amount', '>', 0)
                                        ->sum('amount');
                                @endphp
                                
                                @if($loyaltyEarned > 0)
                                <tr>
                                    <td style="padding: 0 32px 20px 32px;">
                                        <table role="presentation" width="100%" cellpadding="0" cellspacing="0" border="0" 
                                               bgcolor="#dcfce7" style="background-color: #dcfce7; border-radius: 12px; border: 1px solid #bbf7d0;">
                                            <tr>
                                                <td style="padding: 14px 20px; text-align: center;">
                                                    <span style="font-size: 13px; color: #166534;">
                                                        ✨ {{ __('order-email-loyalty-earned', ['points' => number_format($loyaltyEarned, 2)]) }}
                                                    </span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                @endif
                                
                                <!-- Divider -->
                                <tr>
                                    <td style="padding: 0 32px;">
                                        <div style="height: 1px; background-color: #e0e0e0; font-size: 1px; line-height: 1px;">&nbsp;</div>
                                    </td>
                                </tr>
                                
                                <!-- Footer -->
                                <tr>
                                    <td bgcolor="#f5f5f5" style="background-color: #f5f5f5; padding: 20px 32px; border-radius: 0 0 15px 15px;">
                                        <p style="margin: 0 0 4px 0; font-size: 12px; color: #666666; text-align: center;">
                                            {{ __('order-email-thanks') }},
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
