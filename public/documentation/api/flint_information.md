# REST API: Flint Project

## Resource URI

    https://espace.library.uq.edu.au/api/flint.php

## Resource Properties

A list of languages or interviewees related to the flint project.

https://espace.library.uq.edu.au/api/flint.php?languages=1

| Property    | Description
| ----------- | -----------
| cvo_title   | Language Name
| cvo_desc			| Language Description / Language Data
| cvo_lat | Approximate location the language was used. Latitude
| cvo_long | Approximate location the language was used. Longitude
| record_count | Number of records with this language

```json
[
    {
        "cvo_id": "453670",
        "cvo_title": "Yukulta / Gangalidda",
        "cvo_desc": "Qld SE54-05",
        "cvo_image_filename": null,
        "cvo_external_id": "G34",
        "cvo_hide": "0",
        "cvo_order": null,
        "cvo_lat": "-17.942726",
        "cvo_long": "138.829422",
        "record_count": "1"
    },
    {
        "cvo_id": "453671",
        "cvo_title": "Garrwa / Garrawa / Garawa / Karawa",
        "cvo_desc": "NT SE53-08",
        "cvo_image_filename": null,
        "cvo_external_id": "N155",
        "cvo_hide": "0",
        "cvo_order": null,
        "cvo_lat": "-17.190651",
        "cvo_long": "138.03772",
        "record_count": "1"
    },
    .... 
]
```

https://espace.library.uq.edu.au/api/flint.php?interviewees=1

| Property    | Description
| ----------- | -----------
| rek_contributor   | Name of the interviewee
| interviewee_count			| Number of records the interviewee is part of.

```json
[
    {
        "cvo_id": "453670",
        "cvo_title": "Yukulta / Gangalidda",
        "cvo_desc": "Qld SE54-05",
        "cvo_image_filename": null,
        "cvo_external_id": "G34",
        "cvo_hide": "0",
        "cvo_order": null,
        "cvo_lat": "-17.942726",
        "cvo_long": "138.829422",
        "record_count": "1"
    },
    {
        "cvo_id": "453671",
        "cvo_title": "Garrwa / Garrawa / Garawa / Karawa",
        "cvo_desc": "NT SE53-08",
        "cvo_image_filename": null,
        "cvo_external_id": "N155",
        "cvo_hide": "0",
        "cvo_order": null,
        "cvo_lat": "-17.190651",
        "cvo_long": "138.03772",
        "record_count": "1"
    },
    .... 
]
```

## HTTP GET

Returns an academic's trending publications from eSpace, where {id} the UQ username of the academic.


If the request is successful a HTTP 200 response is returned with the following response body:

An optional query parameter can be added for JSONP requests
([read more](https://github.com/uqlibrary/uqlapp/blob/master/docs/api/jsonp-callback.md)):

    https://espace.library.uq.edu.au/api/flint.php?callback=aCallbackFn&lanuages=1