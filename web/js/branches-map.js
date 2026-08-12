(function () {
    'use strict';

    var NERO_BRANCHES = [
        {
            name: 'NERO Premium — Temur Malik filiali',
            address: 'Toshkent sh., Temur Malik ko\'chasi',
            coords: [41.331057, 69.359197],
            phone: '+998992552225',
        },
        {
            name: 'Carvon',
            address: 'Toshkent sh., Salar bo\'yi ko\'chasi, 47',
            coords: [41.330066, 69.313136],
            phone: '+998955555252',
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
