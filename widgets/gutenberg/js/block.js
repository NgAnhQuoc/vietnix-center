/**
 * Vietnix
 * JS Register Gutenberg Block Editor.
 */
//const { registerBlockType } = wp.blocks;
(function(exports){
  const { RichText } = wp.blockEditor;
  const { registerBlockType } = wp.blocks;

  registerBlockType('vnx/note-block', {
    title: 'VNX Note',
    icon: 'format-status',
    category: 'text',
    attributes: {
      editText: {
        type: 'string',
        source: 'html',
        selector: 'p'
      },

    },
    edit: function (props) {
      const styleBackgroud = {
        'border-left': '3px solid black',
        padding: '20px'
      }
      function updateText(event) { props.setAttributes({ editText: event }) }
      return React.createElement("div", {
        style: styleBackgroud,
        class: "input-vnx-note"
      }, React.createElement(RichText, {
        key: "editcontent",
        tagName: "div",
        value: props.attributes.editText,
        onChange: updateText,
        placeholder: "Input your Note"
      }));
    },
    save: function (props) {
      return React.createElement("div", {

      }, React.createElement(RichText.Content, {
        tagName: "p",
        value: props.attributes.editText,
      }));
    },
  },
  )
})(window);