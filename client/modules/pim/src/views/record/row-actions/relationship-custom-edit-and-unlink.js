

Espo.define('pim:views/record/row-actions/relationship-custom-edit-and-unlink', 'views/record/row-actions/default',
    Dep=> Dep.extend({

        getActionList: function () {
            let list = [];
            if (this.options.acl.edit) {
                list = list.concat([
                    {
                        action: 'quickEditCustom',
                        label: 'Edit',
                        data: {
                            id: this.model.id
                        }
                    },
                    {
                        action: 'unlinkRelatedCustom',
                        label: 'Unlink',
                        data: {
                            id: this.model.id
                        }
                    }
                ]);
            }
            return list;
        },

    })
);


