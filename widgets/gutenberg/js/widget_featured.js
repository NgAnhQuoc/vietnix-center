/**
 * @VNX
 * Create Block Editor "Box Featured Snippet"
 * 16-09-2022
 */
(function(exports){
var { InnerBlocks } = wp.blockEditor;
var { registerBlockType } = wp.blocks;

const ALLOWED_BLOCKS = ['core/list', 'core/heading', "core/paragraph"];

const MY_TEMPLATE = [
  ['core/heading', { placeholder: 'Adding Featured...' }],
  ['core/list', { placeholder: 'Adding detail featured...' },],
]

var el = wp.element.createElement;

registerBlockType("vnx/featured-snippet", {
  title: "VNX Featured Snippet",
  icon: "format-status",
  category: "text",
  attributes: {},
  edit: function (props) {
    const backgroud_block = {
      'border-left': "4px solid black",
      padding: '20px'
    }

    return [
      el(
        "div",
        {
          class: "input-vnx-featured-snippet",
          style: backgroud_block
        },
        el(InnerBlocks, {
          allowedBlocks: ALLOWED_BLOCKS,
          template: MY_TEMPLATE,
        }),
      )
    ];
  },
  save: function (props) {
    return el(
      "div", {},
      el(InnerBlocks.Content, {}),
    );
  },
});
})(window);