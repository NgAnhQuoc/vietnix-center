(function(exports){
const { registerBlockType: registerBlockType3 } = wp.blocks;
const { Fragment, useEffect } = wp.element;
const { InspectorControls } = wp.blockEditor;

// Schema attribute va save() PHAI giong het vietnix-plugin: block duoc luu thang vao
// post_content, lech default hay HTML save la mo bang plugin kia se bao "invalid content".
const vnxShortcodeOf = (templateType, template) => templateType === 'elementor'
    ? `[elementor-template id="${template}"]`
    : `[bricks_template id="${template}"]`;

registerBlockType3('vnx/shortcode-widget-block', {
    title: 'VNX Shortcode',
    icon: 'shortcode',
    category: 'text',
    attributes: {
        template: {
            type: 'string',
            default: '',
        },
        templateType: {
            type: 'string',
            default: 'elementor', // giong vietnix-plugin
        },
        templateOptions: {
          type: 'array',
          default: ['nodata']
        }
      },
    edit: (props) => {
        const { attributes, setAttributes } = props;
        const { template, templateType, templateOptions } = attributes;

        // Center chi con Bricks. Block moi (chua chon template) thi ghi ro templateType = 'bricks'
        // - khac default 'elementor' nen attribute duoc serialize vao noi dung, vietnix-plugin
        // mo lai van hieu dung la Bricks. Block Elementor cu da chon template thi giu nguyen.
        useEffect(() => {
            if (!template && templateType !== 'bricks') {
                setAttributes({ templateType: 'bricks' });
            }
        }, []);

        function onUpdateTemplate(event) {
            setAttributes({ template: event.target.value });
        }

        function onUpdateTemplateType(event) {
            setAttributes({ templateType: event.target.value });
            setAttributes({ template: '' });
            fetchTemplateOptions(event.target.value);
        }
        const fetchTemplateOptions = async (templateType) => {
          try {
            const response = await fetchData(templateType)
            if(response != 'nodata'){
                setAttributes({ templateOptions: response });
            }else{
                setAttributes({ templateOptions: ['nodata'] });
            }
          } catch (error) {
            console.error(error);
          }
        };
        const getTemplateList = (templateType) => {
          if(templateOptions != undefined && templateOptions.id != undefined && Object.keys(templateOptions.id).length){
            const elements = templateOptions.id.map((item, index) => {
                if(item == -1){
                    return
                }
                else{
                    return React.createElement('option', { value: item, key: item }, templateOptions.name[index]);
                }
            })
            return elements;
          }else{
            fetchTemplateOptions(templateType)
          }
        }
        return (
            React.createElement(
                Fragment,
                null,
                React.createElement(
                    'div',
                    {
                        className: 'block-editor-block-list__block wp-block components-placeholder is-selected wp-block-shortcode',
                    },
                    React.createElement('h3', { htmlFor: 'select_template' }, 'ShortCode: '),
                        React.createElement('code', null, template ? vnxShortcodeOf(templateType, template) : 'Not selected')
                ),
                React.createElement(InspectorControls, {},
                    React.createElement('div', {
                        className: 'components-panel__body is-opened',
                    },
                        React.createElement('h3', { htmlFor: 'select_template_type' }, 'Loại template:'),
                        React.createElement('select', {
                            id: 'select_template_type',
                            value: templateType,
                            onChange: (newFirstChoice) => {
                                onUpdateTemplateType(newFirstChoice);
                            }
                        },
                            // Chi hien lua chon Elementor cho block cu dang dung no, de UI khop du lieu.
                            templateType === 'elementor' &&
                                React.createElement('option', { value: 'elementor' }, 'Elementor (cũ)'),
                            React.createElement('option', { value: 'bricks' }, 'Bricks')
                        ),

                        templateType === 'elementor' &&
                            React.createElement('p', { className: 'components-panel__row' }, template
                                ? `Template Elementor #${template} - chọn Bricks để đổi sang template Bricks.`
                                : 'Chọn Bricks để gắn template.'),
                        templateType === 'bricks' &&
                            React.createElement('label', { htmlFor: 'select_template', className: 'components-panel__row' }, 'Chọn template Bricks: '),
                        templateType === 'bricks' &&
                            React.createElement('select', {
                                id: 'select_template',
                                value: template,
                                onChange: onUpdateTemplate,
                            },
                                React.createElement('option', { value: '' }, 'Unset'),
                                getTemplateList('bricks')
                            )
                    )
                )
            )
        );
    },
    save: (props) => {
        const { template, templateType } = props.attributes;
        const shortcode = vnxShortcodeOf(templateType, template);

        return React.createElement(
            'div',
            null,
            shortcode
        );
    },
});

const fetchData = async (template_type) =>{
    var admin_ajax_url = window.location.origin + '/wp-admin/admin-ajax.php';
    try {
        let data_res = [];
        const formData = new FormData();
        formData.append('action', 'vnx_get_template_list_center');
        formData.append('template_type',  template_type);
        formData.append('category',  'single-post-price-table');
        const response = await fetch(admin_ajax_url ,{
            method: 'POST',
            body: formData,
          }).then( function (response) {
            if(response.ok) {
                return response.json();
            }
            return Promise.reject(response);
        }).then(function (data) {
            if(typeof data.data.data.length != undefined && data.data.data.length == 0){
                data_res = 'nodata';
            }
            else{
                data_res = data.data.data;
            }
        }).catch(function (error) {
            console.warn('Error', error);
        });
        return data_res;
        }
        catch (error) {
          console.error('Error:', error.message);
        }
}

})(window);
