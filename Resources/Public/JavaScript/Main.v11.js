(function () {
    // CSS query selector of parent elements with needed data attributes
    const _classWrapperColumns = '.t3js-page-column, .t3js-page-ce';
    // CSS query selector of the panel where to insert the add reference button
    const _classWrapperAddElement = '.t3js-page-new-ce';
    // CSS query selector which triggers the insert via delegate -> click
    const _clicker = '[data-add-reference="1"]';
    // First container with page uid
    const _page_infos = document.querySelector('[data-page]');
    // The function object
    const SIMPLE_REFERENCE = {
        /**
         * The needed initial functions, called at the end of this function object
         * See last lines here
         */
        documentReady: function () {
            SIMPLE_REFERENCE.initialRequest({}, false);
            SIMPLE_REFERENCE.initializeButtonClick();
        },
        /**
         * Add the paste reference button to desired div containers...
         */
        addReferenceButtons: function () {
            const _contentColumns = document.querySelectorAll(_classWrapperColumns);

            if (_contentColumns.length) {
                for (let _contentElement of _contentColumns) {
                    const _panelAddElement = _contentElement.querySelectorAll(':scope > .t3js-page-ce > ' + _classWrapperAddElement + ',:scope > ' + _classWrapperAddElement);
                    if (_panelAddElement.length) {
                        for (let _panel of _panelAddElement) {
                            if (!_panel.querySelector('[data-add-reference="1"]')) {
                                const button = SIMPLE_REFERENCE.helper.buttonHtml(_contentElement, _panel);
                                //_panel.style.background= "blue";
                                _panel.insertAdjacentHTML('beforeend', button);
                            }
                        }
                    }
                }
            }
        },
        /**
         * Simple delegate click function
         */
        initializeButtonClick: function () {
            document.addEventListener('click', function (event) {
                const isClicker = event.target.matches
                    ? event.target.matches(_clicker)
                    : event.target.msMatchesSelector(_clicker);

                if (isClicker && event.target.dataset) {
                    event.preventDefault();
                    SIMPLE_REFERENCE.initialRequest(event.target.dataset, event.target);
                }
            }, false);
        },
        /**
         * The initial request to detect if elements are on the clipboard
         * @param {object} request A valid object
         * @param {object} el A valid object
         */
        initialRequest: function (request, el) {
            require(['TYPO3/CMS/Core/Ajax/AjaxRequest'], function (AjaxRequest) {
                new AjaxRequest(TYPO3.settings.ajaxUrls.simplereference_get_data)
                    .post(request)
                    .then(
                        async function (response) {
                            const data = await response.resolve();
                            if (typeof data === "object" && data !== null && "references" in data) {
                                SIMPLE_REFERENCE.addReferenceButtons();
                                if ("data" in data) {
                                    //SIMPLE_REFERENCE.insertReference(data);
                                    SIMPLE_REFERENCE.openModal(data);
                                }
                            }
                        }
                    );
            });
        },
        /**
         *  Calls record_process route
         *  @param {object} request A valid object with data and optional redirectt
         */
        insertReference: function (request) {
            require(['TYPO3/CMS/Core/Ajax/AjaxRequest'], function (AjaxRequest) {
                new AjaxRequest(TYPO3.settings.ajaxUrls.record_process)
                    .post(request)
                    .then(
                        async function (response) {
                            const response_data = await response.resolve();
                            if (typeof response_data === "object" && "redirect" in response_data) {
                                location.href = response_data.redirect;
                                //document.getElementById("#element-tt_content-288").scrollIntoView();
                            }
                        }
                    );
            });
        },
        /**
         * Creates the paste or cancel modal
         * @param {object} data A valid object with data and optional redirectt
         */
        openModal: function (data) {
            require(['TYPO3/CMS/Backend/Modal',
                'TYPO3/CMS/Backend/Element/IconElement',
                "TYPO3/CMS/Backend/Severity",
                "TYPO3/CMS/Backend/Enum/Severity"], function (Modal, Icon, Css, CssEnum) {

                const _modalTitle = (TYPO3.lang["simplereference.modal.title"] || "Create Reference"), // + ': "' + this.itemOnClipboardTitle + '"',
                    _modalText = TYPO3.lang["simplereference.modal.text"] || "Do you want to paste a reference to this position?",
                    _modalButtons = [{
                        text: TYPO3.lang["simplereference.modal.button.cancel"] || "Cancel",
                        active: !0,
                        btnClass: "btn-default",
                        trigger: function () {
                            Modal.currentModal.trigger("modal-dismiss")
                        }
                    }, {
                        text: TYPO3.lang["simplereference.modal.button.paste"] || "Paste",
                        btnClass: "btn-" + Css.getCssClass(CssEnum.SeverityEnum.warning),
                        trigger: function () {
                            Modal.currentModal.trigger("modal-dismiss");
                            SIMPLE_REFERENCE.insertReference(data);
                        }
                    }];
                Modal.show(_modalTitle, _modalText, CssEnum.SeverityEnum.warning, _modalButtons)
            });
        },
        helper: {
            /**
             * Creates the paste or cancel modal
             * To get the page uid you can use here maybe a split on _panel.id, too
             * @param {HTMLElement} _contentElement The parent content element or column
             * @param {HTMLElement} _panel The panel where the button is added
             */
            buttonHtml: function (_contentElement,_panel) {

                if (!_page_infos) {
                    return  '';
                }
                return '<span class="btn btn-default btn-sm" data-add-reference="1" ' +
                    'data-add-panel-id="' + _panel.id + '" ' +
                    'data-add-page-uid="' + _page_infos.dataset.page + '" ' +
                    SIMPLE_REFERENCE.helper.objToString(_contentElement.dataset) +
                    'title="' + (TYPO3.lang["simplereference.modal.title"] || "Create Reference") + '" ' +
                    '>' +
                    '<span class="icon icon-size-small " style="pointer-events: none">' +
                    '<span class="icon-markup">' +
                    '<svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" viewBox="0 0 16 16" class="icon-color">' +
                    '<path d="M1,1v14h7v-1H2V2h12v6h1V1H1z"/>' +
                    '<rect x="3" y="6" width="10" height="1"/>' +
                    '<rect x="3" y="8" width="5" height="1"/>' +
                    '<rect x="3" y="10" width="5" height="1"/>' +
                    '<rect x="3" y="12" width="5" height="1"/>' +
                    '<path d="M3,3h10v2H3V3z"/>' +
                    '<path d="M9,9h7v7H9V9z"/>' +
                    '<path fill="#FFFFFF" d="M10,10h5v5h-5V10z"/>' +
                    '<path d="M11,13.1c0,0.1,0,0.2,0,0.3c0,0.1,0.1,0.2,0.1,0.3c0,0.1,0.1,0.2,0.1,0.3c0,0.1,0.1,0.2,0.1,0.2l0,0.1c0,0,0,0,0.1,0 ' +
                    'c0,0,0,0,0,0c0,0,0.1,0,0-0.1c-0.1-0.6,0-1,0.2-1.2c0.1-0.1,0.2-0.2,0.4-0.2c0.2-0.1,0.3-0.1,0.6-0.1v0.5c0,0.1,0,0.1,0.1,0.1 ' +
                    'c0,0,0,0,0.1,0c0,0,0.1,0,0.1,0l1.1-1.1c0,0,0-0.1,0-0.1s0-0.1,0-0.1l-1.1-1.1c0,0-0.1-0.1-0.1,0c-0.1,0-0.1,0.1-0.1,0.1v0.6 ' +
                    'c-0.6,0-1,0.2-1.3,0.5C11.1,12.3,11,12.6,11,13.1z"/>' +
                    '</svg>' +
                    '</span>' +
                    '</span>' +
                    '</span>';
            },
            objToString: function (obj) {
                let str = '';
                for (const [p, val] of Object.entries(obj)) {
                    str += ` data-add-${p}="${val}" `;
                }
                return str;
            }
        }
    }
    // do it
    SIMPLE_REFERENCE.documentReady();
})();
