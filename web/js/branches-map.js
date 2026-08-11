(function () {
    'use strict';

    // Sample Tashkent branches — replace with real locations/coordinates/phones.
    var NERO_BRANCHES = [
        {
            name: 'NERO — Chilonzor filiali',
            address: 'Toshkent sh., Chilonzor tumani, Bunyodkor shoh ko\'chasi, 12-uy',
            coords: [41.284, 69.204],
            phone: '+998900000001',
        },
        {
            name: 'NERO — Yunusobod filiali',
            address: 'Toshkent sh., Yunusobod tumani, Amir Temur shoh ko\'chasi, 45-uy',
            coords: [41.338, 69.288],
            phone: '+998900000002',
        },
        {
            name: 'NERO — Mirzo Ulug\'bek filiali',
            address: 'Toshkent sh., Mirzo Ulug\'bek tumani, Buyuk Ipak Yo\'li ko\'chasi, 78-uy',
            coords: [41.311081, 69.240562],
            phone: '+998900000003',
        },
    ];

    // Invoked by the Yandex Maps API script's own onload= callback param once
    // api-maps.yandex.ru has finished loading (see views/site/_branches.php) —
    // this runs regardless of whether that script tag finished before or after
    // this file, since the API calls it itself rather than us racing to detect it.
    window.initNeroMap = function () {
        ymaps.ready(renderNeroMap);
    };

    function renderNeroMap() {
        var container = document.getElementById('nero-map');
        if (!container) {
            return;
        }

        var map = new ymaps.Map(container, {
            center: NERO_BRANCHES[0].coords,
            zoom: 11,
            controls: ['zoomControl', 'fullscreenControl'],
        });

        var placemarks = NERO_BRANCHES.map(function (branch) {
            var placemark = new ymaps.Placemark(
                branch.coords,
                {
                    balloonContentHeader: branch.name,
                    balloonContentBody:
                        '<div>' + branch.address + '</div>' +
                        '<div><a href="tel:' + branch.phone + '">' + branch.phone + '</a></div>',
                },
                { preset: 'islands#redIcon' },
            );
            map.geoObjects.add(placemark);
            return placemark;
        });

        if (placemarks.length > 1) {
            map.setBounds(map.geoObjects.getBounds(), { checkZoomRange: true, zoomMargin: 40 });
        }
    }
})();
