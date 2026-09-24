window.jQuery = window.$ = jQuery;
function add_code_block_section_label(elem) {
    if (!elem) return;
    label = '<div class="code_block_label">Code</div>';
    if ($(elem).hasClass("wp-block-code")) {
        $(elem)
            .parent()
            .before(label);
    } else {
        $(elem).before(label);
    }
}
function vnx_copy_code_block(hcbWraps) {
    if (!window.ClipboardJS) return;
    let clipCt = 1;
    for (let i = 0; i < hcbWraps.length; i++) {
        const elem = hcbWraps[i];
        const code = elem.querySelector("code");
        if (null === code) continue;
        if ($(elem).hasClass("wp-block-code")) {
            $(elem).wrapAll("<div class='vnx_wrap'></div>");
            $(elem).addClass("line-numbers");
            $(code).addClass("language-html");
        }
        // add copy button - open
        const button = document.createElement("button");
        button.classList.add("vnx-clipboard", "vnx_copy");
        const copy_txt = '<span class="no_copied">Copy</span>';
        const copied_txt = '<span class="copied">Copied!</span>';
        $(button).html(copy_txt + copied_txt);
        button.setAttribute(
            "data-clipboard-target",
            '[data-vnx-clip="' + clipCt + '"]'
        );
        button.setAttribute("data-clipboard-action", "copy");
        button.setAttribute("aria-label", window.hcbVars?.copyBtnLabel || "");
        if ($(elem).hasClass("wp-block-code")) {
            elem.before(button);
        } else {
            elem.prepend(button);
        }
        // add copy button - close

        // codeタグにターゲット属性追加
        code.setAttribute("data-vnx-clip", clipCt);

        if (window.Prism) Prism.highlightElement(code, "");
        // add_code_block_section_label(elem);
        clipCt++;
    }
    const clipboard = new ClipboardJS(".vnx-clipboard");
    clipboard.on("success", function(e) {
        const btn = e.trigger;
        btn.classList.add("done");
        setTimeout(() => {
            btn.classList.remove("done");
        }, 5000);
    });
    // clipboard.on('error', function (e) {
    // 	alert(e);
    // });
}
$(document).ready(function() {
    if (!window.ClipboardJS) return;
    let hcbWraps = document.querySelectorAll(".hcb_wrap,.wp-block-code"); // select 2 element class to run vnx_copy_code_block
    vnx_copy_code_block(hcbWraps);
});
