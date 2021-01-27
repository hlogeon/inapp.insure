<meta http-equiv="Content-type" content="text/html; charset=utf-8" />
<meta http-equiv="X-UA-Compatible" content="ie=edge" />
<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0" />
<link rel="shortcut icon" href="https://inapp.insure/images/favicon.ico" type="image/x-icon">

<link href="<?=asset('css/main.css')?>" rel="stylesheet">
<link href="<?=asset('css/app.css')?>" rel="stylesheet">
<link href="<?=asset('css/style.css')?>" rel="stylesheet">
<script src="https://maps.googleapis.com/maps/api/js?key=AIzaSyAME8iXmxU06IdGtQMQ2Cd9E2mgEnxhibQ&libraries=places"></script>
<script>
    var url = "https://suggestions.dadata.ru/suggestions/api/4_1/rs/suggest/address";
    var token = "8d1c08255520ec802c2e54f56727d1da384aa7bd";
    var query = "хабар";

    var options = {
        method: "POST",
        mode: "cors",
        headers: {
            "Content-Type": "application/json",
            "Accept": "application/json",
            "Authorization": "Token " + token
        },
        body: JSON.stringify({query: query})
    }

    fetch(url, options)
        .then(response => response.text())
        .then(result => console.log(result))
        .catch(error => console.log("error", error));
</script>