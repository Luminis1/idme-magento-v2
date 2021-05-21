define([
    'uiComponent',
    'jquery',
    'Magento_Customer/js/customer-data',
    'Magento_Checkout/js/model/quote',
    'Magento_Checkout/js/model/cart/totals-processor/default',
    'Magento_Ui/js/modal/modal',
], function (Component, $, customerData, quote, totalsDefault, modal) {
    'use strict';

    return Component.extend({
        defaults: {
            template: 'IDme_GroupVerification/checkout/idme',
        },

        initialize: function () {
            this._super();
            this.idmeVerify = customerData.get('idme-verify');
            if (this.idmeVerify._latestValue.website_id !== window.checkout.websiteId) {
                customerData.reload(['idme-verify'], false);
            }
        },

        popupVerify: function (child) {
            var top = ($(document).height() - 780) / 4;
            var left = ($(document).width() - 750) / 2;
            var startUrl = customerData.get('idme-verify')._latestValue.startUrl;

            var idmeObserver = new MutationObserver(function (mutations) {
                mutations.forEach(function (mutation) {
                    customerData.reload('idme-verify');
                    totalsDefault.estimateTotals(quote.shippingAddress);
                    idmeObserver.disconnect();
                });
            });

            /* we don't need to handle this, it just sets verify_started */
            $.ajax({
                    url: startUrl,
                    type: 'GET',
                    async: true,
                    dataType: 'json',
                    data: '',
                }
            );

            window.open(child.popup_url + "&display=popup", "", "scrollbars=yes,menubar=no,status=no,location=no,toolbar=no,width=750,height=780,top=" + top + ",left=" + left);

            idmeObserver.observe(document.getElementById('idmeVerify'), {
                attributes: true,
                characterData: true,
                childList: true,
                subtree: true,
                attributeOldValue: true,
                characterDataOldValue: true
            });

        },

        removeVerification: function () {
            return $.ajax({
                    url: this.idmeVerify._latestValue.removeUrl,
                    type: 'GET',
                    async: false,
                    dataType: 'json',
                    data: ''
                }
            ).done(function (response) {
                if (response) {
                    if (response.code == 'ok') {
                        customerData.reload('idme-verify');
                        customerData.reload('cart');
                        totalsDefault.estimateTotals(quote.shippingAddress);
                    }
                }
            });
        },

        getModal: function () {
            var options = {
                buttons : [{
                    text: 'Close',
                    click: function () {
                        this.closeModal();
                    },
                }],
                type : 'popup',
            };

            var aboutModal = modal(options, $('#idme-modal-content'));
            $("#idme-modal-content").modal('openModal');
        },

    });
});