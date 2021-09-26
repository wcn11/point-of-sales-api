<!DOCTYPE html>
<html lang="en" xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml"
      xmlns:o="urn:schemas-microsoft-com:office:office">

<head>
    <meta charset="utf-8"> <!-- utf-8 works for most cases -->
    <meta name="viewport" content="width=device-width"> <!-- Forcing initial-scale shouldn't be necessary -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge"> <!-- Use the latest (edge) version of IE rendering engine -->
    <meta name="x-apple-disable-message-reformatting"> <!-- Disable auto-scale in iOS 10 Mail entirely -->
    <title></title> <!-- The title tag shows in email notifications, like Android 4.4. -->

    <!-- <link href="https://fonts.googleapis.com/css?family=Work+Sans:200,300,400,500,600,700" rel="stylesheet"> -->

    <!-- CSS Reset : BEGIN -->
    <style>
        /* What it does: Remove spaces around the email design added by some email clients. */
        /* Beware: It can remove the padding / margin and add a background color to the compose a reply window. */
        html,
        body {
            margin: 0 auto !important;
            padding: 0 !important;
            height: 100% !important;
            width: 100% !important;
            background: #f1f1f1;
        }

        /* What it does: Stops email clients resizing small text. */
        * {
            -ms-text-size-adjust: 100%;
            -webkit-text-size-adjust: 100%;
        }

        /* What it does: Centers email on Android 4.4 */
        div[style*="margin: 16px 0"] {
            margin: 0 !important;
        }

        /* What it does: Stops Outlook from adding extra spacing to tables. */
        table,
        td {
            mso-table-lspace: 0pt !important;
            mso-table-rspace: 0pt !important;
        }

        /* What it does: Fixes webkit padding issue. */
        table {
            border-spacing: 0 !important;
            border-collapse: collapse !important;
            table-layout: fixed !important;
            margin: 0 auto !important;
        }

        /* What it does: Uses a better rendering method when resizing images in IE. */
        img {
            -ms-interpolation-mode: bicubic;
        }

        /* What it does: Prevents Windows 10 Mail from underlining links despite inline CSS. Styles for underlined links should be inline. */
        a {
            text-decoration: none;
        }

        /* What it does: A work-around for email clients meddling in triggered links. */
        *[x-apple-data-detectors],
            /* iOS */
        .unstyle-auto-detected-links *,
        .aBn {
            border-bottom: 0 !important;
            cursor: default !important;
            color: inherit !important;
            text-decoration: none !important;
            font-size: inherit !important;
            font-family: inherit !important;
            font-weight: inherit !important;
            line-height: inherit !important;
        }

        /* What it does: Prevents Gmail from displaying a download button on large, non-linked images. */
        .a6S {
            display: none !important;
            opacity: 0.01 !important;
        }

        /* What it does: Prevents Gmail from changing the text color in conversation threads. */
        .im {
            color: inherit !important;
        }

        /* If the above doesn't work, add a .g-img class to any image in question. */
        img.g-img+div {
            display: none !important;
        }

        /* What it does: Removes right gutter in Gmail iOS app: https://github.com/TedGoas/Cerberus/issues/89  */
        /* Create one of these media queries for each additional viewport size you'd like to fix */

        /* iPhone 4, 4S, 5, 5S, 5C, and 5SE */
        @media only screen and (min-device-width: 320px) and (max-device-width: 374px) {
            u~div .email-container {
                min-width: 320px !important;
            }
        }

        /* iPhone 6, 6S, 7, 8, and X */
        @media only screen and (min-device-width: 375px) and (max-device-width: 413px) {
            u~div .email-container {
                min-width: 375px !important;
            }
        }

        /* iPhone 6+, 7+, and 8+ */
        @media only screen and (min-device-width: 414px) {
            u~div .email-container {
                min-width: 414px !important;
            }
        }
    </style>

    <!-- CSS Reset : END -->

    <!-- Progressive Enhancements : BEGIN -->
    <style>
        .primary {
            background:rgb(206, 23, 0);
        }

        .bg_white {
            background: #ffffff;
        }

        .bg_light {
            background: #f7fafa;
        }

        .bg_black {
            background: #000000;
        }

        .bg_dark {
            background: rgba(0, 0, 0, .8);
        }

        .email-section {
            padding: 2.5em;
        }

        /*BUTTON*/
        .btn {
            padding: 10px 15px;
            display: inline-block;
        }

        .btn.btn-primary {
            border-radius: 5px;
            background: rgb(206, 23, 0);
            color: #ffffff;
        }

        .btn.btn-white {
            border-radius: 5px;
            background: #ffffff;
            color: #000000;
        }

        .btn.btn-white-outline {
            border-radius: 5px;
            background: transparent;
            border: 1px solid #fff;
            color: #fff;
        }

        .btn.btn-black-outline {
            border-radius: 0px;
            background: transparent;
            border: 2px solid #000;
            color: #000;
            font-weight: 700;
        }

        .btn-custom {
            color: rgba(0, 0, 0, .3);
            text-decoration: underline;
        }

        h1,
        h2,
        h3,
        h4,
        h5,
        h6 {
            font-family: 'Work Sans', sans-serif;
            color: #000000;
            margin-top: 0;
            font-weight: 400;
        }

        body {
            font-family: 'Work Sans', sans-serif;
            font-weight: 400;
            font-size: 15px;
            line-height: 1.8;
            color: rgba(0, 0, 0, .4);
        }

        a {
            color: rgb(206, 23, 0);
        }

        table {}

        /*LOGO*/

        .logo h1 {
            margin: 0;
        }

        .logo h1 a {
            color: rgb(206, 23, 0);
            font-size: 24px;
            font-weight: 700;
            font-family: 'Work Sans', sans-serif;
        }

        /*HERO*/
        .hero {
            position: relative;
            z-index: 0;
        }

        .hero .text {
            color: rgba(0, 0, 0, .3);
        }

        .hero .text h2 {
            color: #000;
            font-size: 34px;
            margin-bottom: 15px;
            font-weight: 300;
            line-height: 1.2;
        }

        .hero .text h3 {
            font-size: 24px;
            font-weight: 200;
        }

        .hero .text h2 span {
            font-weight: 600;
            color: #000;
        }


        /*PRODUCT*/
        .product-entry {
            display: block;
            position: relative;
            float: left;
            padding-top: 20px;
        }

        .product-entry .text h3 {
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .product-entry .text p {
            margin-top: 0;
        }

        .product-entry img,
        .product-entry .text {
            float: left;
        }

        ul.social {
            padding: 0;
        }

        ul.social li {
            display: inline-block;
            margin-right: 10px;
        }

        /*FOOTER*/

        .footer {
            border-top: 1px solid rgba(0, 0, 0, .05);
            color: rgba(0, 0, 0, .5);
        }

        .footer .heading {
            color: #000;
            font-size: 20px;
        }

        .footer ul {
            margin: 0;
            padding: 0;
        }

        .footer ul li {
            list-style: none;
            margin-bottom: 10px;
        }

        .footer ul li a {
            color: rgba(0, 0, 0, 1);
        }


        @media screen and (max-width: 500px) {}
    </style>


</head>

<body width="100%" style="margin: 0; padding: 0 !important; mso-line-height-rule: exactly; background-color: #f1f1f1;">
<center style="width: 100%; background-color: #f1f1f1;">
    <div
        style="display: none; font-size: 1px;max-height: 0px; max-width: 0px; opacity: 0; overflow: hidden; mso-hide: all; font-family: sans-serif;">
        &zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;&zwnj;&nbsp;
    </div>
    <div style="max-width: 600px; margin: 0 auto;" class="email-container">
        <!-- BEGIN BODY -->
        <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"
               style="margin: auto;">
            <tr>
                <td valign="top" class="bg_white" style="padding: 1em 2.5em 0 2.5em;">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td class="logo" style="text-align: left;">
                                <img src="data:image/png;base64,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" alt="Logo Beliayam.com">
                            </td>
                        </tr>
                    </table>
                </td>
            </tr><!-- end tr -->
            <tr>
                <td valign="middle" class="hero bg_white" style="padding: 2em 0 2em 0;">
                    <table role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                        <tr>
                            <td style="padding: 0 2.5em; text-align: left;">
                                <div class="text">
                                    <h2 style="text-align: center;">Pemberitahuan Persediaan</h2>
                                    <h3>Gudang: <span style="color: darkred;">{!! $user['name'] !!}</span></h3>
                                    <h4>Permintaan <i>Re-stock</i> Pemesanan Segera</h4>
                                </div>
                            </td>
                        </tr>
                    </table>
                </td>
            </tr><!-- end tr -->
            <tr>
                <table class="bg_white" role="presentation" border="0" cellpadding="0" cellspacing="0" width="100%">
                    <tr style="border-bottom: 1px solid rgba(0,0,0,.05);">
                        <th width="80%"
                            style="text-align:left; padding: 0 2.5em; color: #000; padding-bottom: 20px">Item</th>
                        <th width="20%"
                            style="text-align:right; padding: 0 2.5em; color: #000; padding-bottom: 20px">Total</th>
                    </tr>
                    @foreach($stocks as $stock)
                    <tr style="border-bottom: 1px solid rgba(0,0,0,.05);">
                        <td valign="middle" width="80%" style="text-align:left; padding: 0 2.5em;">
                            <div class="product-entry">
                                <div class="text">
                                    <h3>{{ $stock['name'] }}</h3>
                                    <!-- <span>Small</span>
                                    <p>A small river named Duden flows by their place and supplies it with the
                                        necessary regelialia.</p> -->
                                </div>
                            </div>
                        </td>
                        <td valign="middle" width="20%" style="text-align:left; padding: 0 2.5em; float: right;">
                            <span class="price" style="color: #000; font-size: 20px;">{{ $stock['quantity']  }}</span>
                        </td>
                    </tr>
                    @endforeach
                    <tr>
                        <td valign="middle" style="text-align:left; padding: 1em 2.5em;">
                            <h5><i>NB: Di sarankan untuk membawa persediaan lebih dari permintaan.</i></h5>
                        </td>
                    </tr>
                </table>
            </tr><!-- end tr -->
            <!-- 1 Column Text + Button : END -->
        </table>
        <table align="center" role="presentation" cellspacing="0" cellpadding="0" border="0" width="100%"
               style="margin: auto;">
            <tr>
                <td class="bg_white" style="text-align: center;">
                    <p>@Copyright 2019 beliayam.com
                        All Right Reserved </p>
                </td>
            </tr>
        </table>

    </div>
</center>
</body>

</html>
