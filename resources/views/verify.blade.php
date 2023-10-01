<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Verification</title>
    <script>
        function copyToClipboard(text) {
            var dummy = document.createElement("textarea");
            document.body.appendChild(dummy);
            dummy.value = text;
            dummy.select();
            document.execCommand("copy");
            document.body.removeChild(dummy);
            alert("Verification code copied: " + text);
        }
    </script>
</head>
<body style="font-family: Arial, sans-serif;">

    <div style="background-color: #f4f4f4; padding: 20px; text-align: center;">
        <h2 style="color: #333;">Hello {{ $user }}!</h2>
        <p style="color: #555; font-size: 16px;">Thank you for registering on our website. To verify your account, use the following code:</p>

        <div style="background-color: #ffffff; padding: 10px; display: inline-block; border-radius: 5px; font-size: 20px; color: #333; margin-top: 15px;">
            {{ $code }}
        </div>

        <p style="color: #555; font-size: 16px; margin-top: 15px;">Or click on the following link:</p>
        <a href="javascript:void(0);" onclick="copyToClipboard('{{ $code }}')" style="display: inline-block; background-color: #007bff; color: #ffffff; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-top: 10px; font-size: 18px;">Copy Now</a>
    </div>

</body>
</html>
