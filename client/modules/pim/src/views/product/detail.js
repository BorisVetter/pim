/*
 * This file is part of AtroPIM.
 *
 * AtroPIM - Open Source PIM application.
 * Copyright (C) 2020 AtroCore UG (haftungsbeschränkt).
 * Website: https://atropim.com
 *
 * AtroPIM is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * AtroPIM is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with AtroPIM. If not, see http://www.gnu.org/licenses/.
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU General Public License version 3.
 *
 * In accordance with Section 7(b) of the GNU General Public License version 3,
 * these Appropriate Legal Notices must retain the display of the "AtroPIM" word.
 *
 * This software is not allowed to be used in Russia and Belarus.
 */

Espo.define('pim:views/product/detail', 'pim:views/detail',
    Dep => Dep.extend({

        selectRelatedFilters: {},

        selectBoolFilterLists: {
            attributes: ['notLinkedWithProduct'],
        },

        boolFilterData: {
            attributes: {
                notLinkedWithProduct() {
                    return this.model.id;
                },
            },
        },

        events: _.extend({
            'click a[data-action="setPavAsInherited"]': function (e) {
                let $a = $(e.currentTarget);
                this.ajaxPostRequest(`ProductAttributeValue/action/inheritPav`, {id: $a.data('pavid')}).then(response => {
                    this.notify('Saved', 'success');
                    this.model.trigger('after:attributesSave');
                    $a.parents('.panel').find('.action[data-action=refresh]').click();
                });
            },
        }, Dep.prototype.events),

        actionNavigateToRoot(data, e) {
            e.stopPropagation();

            this.getRouter().checkConfirmLeaveOut(function () {
                const rootUrl = this.options.rootUrl || this.options.params.rootUrl || '#' + this.scope;
                if (rootUrl !== `#${this.scope}`) {
                    this.getRouter().navigate(rootUrl,  {trigger: true});
                } else {
                    const options = {
                        isReturn: true
                    };
                    this.getRouter().navigate(rootUrl, {trigger: false});
                    this.getRouter().dispatch(this.scope, null, options);
                }
            }, this);
        }

    })
);

