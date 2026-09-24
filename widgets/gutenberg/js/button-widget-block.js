(function (exports) {
    const { registerBlockType: registerBlockType2 } = wp.blocks;
    const { Fragment } = wp.element;
    const { InspectorControls } = wp.blockEditor;

    const { ColorPalette } = wp.blockEditor;
    const { RichText } = wp.blockEditor;



    registerBlockType2('vnx/button-widget', {
        title: 'VNX Button',
        icon: 'button',
        category: 'text',
        attributes: {
            text: {
                type: 'string',
                source: 'html',
                selector: 'a',
            },
            typeButton: {
                type: 'string',
                default: ''
            },
            linkHref: {
                type: 'string',
                default: '#'
            },
            justifyText: {
                type: 'string',
                default: 'start'
            }
        },
        edit: (props) => {
            const { attributes, setAttributes } = props;

            function onUpdateText(content) {

                const wrapper = document.createElement('div');
                wrapper.innerHTML = content;
                const links = wrapper.querySelectorAll('a');
                var newText = "";
                if (links.length) {
                    for (let i = 0; i < links.length; i++) {
                        const link = links[i];
                        if (link.getAttribute('href')) {
                            newText = link.innerText;
                            onUpdateLink(link.getAttribute('href'));
                        }
                    }
                } else {
                    newText = content;
                }
                setAttributes({ text: newText });
            }

            function onUpdateTypeButton(event) {
                setAttributes({ typeButton: event.target.value });
            }

            function onUpdateLink(linkHrefs) {
                setAttributes({ linkHref: linkHrefs });
            }

            function onUpdatePosition(event) {
                setAttributes({ justifyText: event.target.value });
            }

            return (
                React.createElement(
                    Fragment,
                    null,
                    React.createElement(
                        'div',
                        {
                            className: 'wp-block-button is-layout-flex',
                            style: {
                                justifyContent: props.attributes.justifyText
                            }
                        },
                        React.createElement('div', {
                            className: 'wp-block-button ' + props.attributes.typeButton,
                        },
                            React.createElement(RichText, {
                                tagName: 'a',
                                className: 'wp-block-button__link wp-element-button ' + props.attributes.typeButton,
                                onChange: onUpdateText,
                                href: props.attributes.linkHref,
                                value: props.attributes.text,
                            })
                        )
                    ),
                    React.createElement(InspectorControls, {},
                        React.createElement('div', null,
                            React.createElement('h3', { htmlFor: 'typeButton' }, 'Type Button: '),
                            React.createElement('select', {
                                id: 'typeButton',
                                value: attributes.typeButton,
                                onChange: onUpdateTypeButton,
                            },
                                React.createElement('option', { value: '' }, 'Mặc định'),
                                React.createElement('option', { value: 'vnx_btn_buy' }, 'Mua ngay'),
                                React.createElement('option', { value: 'vnx_btn_dl' }, 'Download'),
                                React.createElement('option', { value: 'vnx_btn_reg' }, 'Đăng ký'),
                                React.createElement('option', { value: 'vnx_btn_demo' }, 'Demo'),
                                React.createElement('option', { value: 'vnx_btn_seemore' }, 'Xem thêm'),
                            )
                        ),
                        React.createElement('div', null,
                            React.createElement('h3', { htmlFor: 'justifyContent' }, 'Justify Content: '),
                            React.createElement('select', {
                                id: 'justifyContent',
                                value: attributes.justifyText,
                                onChange: onUpdatePosition,
                            },
                                React.createElement('option', { value: 'start' }, 'Start'),
                                React.createElement('option', { value: 'center' }, 'Center'),
                                React.createElement('option', { value: 'end' }, 'End'),
                            )
                        )
                    )
                )
            );
        },
        save: (props) => {
            idValue = '';
            if (props.attributes.typeButton == 'vnx_btn_buy') {
                idValue = 'buy-button-post';
            } else if (props.attributes.typeButton == 'vnx_btn_dl') {
                idValue = 'download-button-post';
            } else if (props.attributes.typeButton == 'vnx_btn_reg') {
                idValue = 'register-button-post';
            } else if (props.attributes.typeButton == 'vnx_btn_demo') {
                idValue = 'demo-button-post';
            } else if (props.attributes.typeButton == 'vnx_btn_seemore') {
                idValue = 'readmore-button-post';
            }
            else {
                idValue = 'default-button-post';
            }

            return (
                React.createElement(
                    'div',
                    {
                        className: 'wp-block-button is-layout-flex',
                        style: {
                            justifyContent: props.attributes.justifyText
                        }
                    },
                    React.createElement('div', {
                        className: 'button-widget wp-block-button ' + props.attributes.typeButton,
                    },
                        React.createElement(RichText.Content, {
                            tagName: 'a',
                            className: 'wp-block-button__link wp-element-button ',
                            href: props.attributes.linkHref,
                            value: props.attributes.text,
                            id: idValue
                        },
                        )
                    )
                )
            );
        },
    });
})(window);