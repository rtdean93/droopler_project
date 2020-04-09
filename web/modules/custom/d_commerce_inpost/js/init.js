/**
 * @file
 */

(function (Drupal, $) {

  var map = {};

  /**
   * Initialize InPost geowidget.
   *
   * @returns {boolean}
   */
  function init() {
    if ($.type(window.easyPack) !== 'undefined') {
      easyPack.init({
        defaultLocale: 'pl',
        mapType: 'osm',
        searchType: 'osm',
        points: {
          types: ['parcel_locker']
        },
        map: {
          initialTypes: ['parcel_locker']
        }
      });
      return true;
    }
    else {
      return false;
    }
  };

  /**
   *
   * @param point
   * @returns {string}
   */
  function getPointAddress(point) {
    return '<span>' +
      Drupal.t('Wybrany paczkomat:') + '<br>' +
      point.name + '<br>' +
      point.address.line1 + '<br>' +
      point.address.line2 + '<br>' +
      '</span>';
  };

  /**
   * Get selected point data.
   */
  function set() {
    if (init() && $.isEmptyObject(map)) {
      map = easyPack.mapWidget("d-commerce-inpost-geowidget", function (point) {
        $('#d-commerce-inpost-point-name').val(point.name);
        $('#d-commerce-inpost-point-address-line1').val(point.address.line1);
        $('#d-commerce-inpost-point-address-line2').val(point.address.line2);
        $('.leaflet-popup').remove();
        $('#d-commerce-inpost-point-address').html(getPointAddress(point))
      });
      map.searchLockerPoint('WAW04A');
    }
  };

  set();

})(Drupal, jQuery);
