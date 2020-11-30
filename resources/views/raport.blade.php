<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>body{font-family:Arial, Helvetica, sans-serif;} </style>
</head>
<body>
    <h1>Raport dostępności i poprawności towarów</h1>
    <p><strong>Godzina wygenerowania raportu: {{\Carbon\Carbon::now()->format('Y-m-d H:i:s')}} </strong></p><br>

    <small>Download_id from softlab: {{$itemsOfRaport[0]["NumberOfDownloadSoftlab"]}} </small><br>
    <small>Download date: {{$itemsOfRaport[0]["DownloadDateSoftlab"]}} </small><br>
        @foreach($itemsOfRaport as $item)
         
                @if(!empty($item["Data"]))
                    <h2>{{$item["NameCompare"]}}</h2>
                    <h2>{{$item["Marketplace"]}}</h2>
                    @foreach($item["Data"] as $dataItem)
                        <ul>
                        @foreach($dataItem as $key => $value)
                            <li><strong>{{$key}}</strong>: {{$value}}</li>
                        @endforeach
                        </ul>
                    @endforeach
                    <small>Download_id from market {{$item["NumberOfDownloadMarket"]}} </small><br>
                    <small>Download date: {{$item["DownloadDateMarket"]}} </small>
                @endif
        @endforeach
</body>
</html>