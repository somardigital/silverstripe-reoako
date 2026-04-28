(function () {
  "use strict";

  (function ($) {
    if (typeof tinymce === "undefined") {
      return;
    }

    tinymce.PluginManager.add("reoakotranslationdialog", function (editor) {

      // Register the icon as an inline SVG (required by TinyMCE 6)
      // Viewbox tightened from "0 0 37 37" to "6 8 24 22" to remove padding and enlarge icon
      editor.ui.registry.addIcon('reoako-icon', '<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="6 8 24 22"><path fill="currentColor" fill-rule="evenodd" d="M 7.783 9.605 C 7.783 8.606 8.592 7.797 9.591 7.797 L 27.673 7.797 C 28.672 7.797 29.481 8.606 29.481 9.605 L 29.481 24.974 C 29.481 25.973 28.672 26.782 27.673 26.782 L 21.069 26.782 L 19.166 28.977 C 18.882 29.305 18.383 29.305 18.097 28.977 L 16.195 26.782 L 9.591 26.782 C 8.592 26.782 7.783 25.973 7.783 24.974 L 7.783 9.605 Z M 17.126 21.858 L 14.981 21.858 L 14.981 12.016 L 18.984 12.016 C 21.291 12.016 22.618 13.274 22.618 15.236 C 22.618 16.579 21.981 17.553 20.827 18.035 L 22.98 21.858 L 20.612 21.858 L 18.692 18.37 L 17.126 18.37 L 17.126 21.858 Z M 17.126 13.72 L 17.126 16.7 L 18.58 16.7 C 19.811 16.7 20.414 16.209 20.414 15.228 C 20.414 14.254 19.811 13.72 18.571 13.72 L 17.126 13.72 Z" clip-rule="evenodd"/></svg>');

      editor.on('click', function(){

          const mceSelection = editor.selection;
          const currentNode = $(mceSelection.getEnd());

          if (currentNode[0]?.nodeName === "REOAKO"){
            const targetNode = currentNode.closest("reoako");
            mceSelection.select(targetNode.get(0));
          }

      });

      function showDialog() {
        var url,
          urlParams,
          mceSelection,
          $currentNode,
          $targetNode,
          insertElement,
          currentText;
        currentText = "";

        mceSelection = editor.selection;
        $currentNode = $(mceSelection.getEnd());
        // target selected embed (if any)
        $targetNode = $currentNode.closest("reoako");

        if ($targetNode.length) {
          currentText = $targetNode.text();
          if ($targetNode[0]?.children.length === 0) {
            // select and replace text-only target
            insertElement = function (elem) {
              mceSelection.select($targetNode.get(0));
              mceSelection.setNode(elem);
            };
          } else {
            // replace attributes of complex target
            insertElement = function (elem) {
              mceSelection.select($targetNode.get(0));
              var $elem = $(elem);
              $targetNode.attr("id", $elem.attr("data-reoako-id"));
            };
          }
        } else {
          if (!mceSelection.isCollapsed()) {
            currentText = mceSelection.getContent({ format: "text" });
          }
          // replace current selection
          insertElement = function (elem) {
            mceSelection.setNode(elem);
          };
        }

        url = "/reoako-modal/search?search_term=" + currentText;

        var instance = editor.windowManager.openUrl({
            title: "Reoako Search",
            url: url,
            width: 800,
            height: 600
        });

        window._reoako = instance;
      }

      // addToggleButton handles both normal and active states in TinyMCE 6
      editor.ui.registry.addToggleButton("reoakotranslationdialog", {
        icon: "reoako-icon",
        tooltip: "Reoako translation dialog",
        onAction: showDialog,
        onSetup: function (api) {
          return editor.selection.selectorChangedWithUnbind('reoako', api.setActive).unbind;
        }
      });

      editor.addCommand("mceTranslationDialog", showDialog);

      var shortcode_regex =
        /(\[reoako )(data-reoako-headword=("(?<headword>.*?)")).((data-reoako-id=("(?<id>.*?)")).)(data-reoako-translation=("(?<translation>.*?)"))(])/gi;

      var reoako_regex =
        /((\<reoako ((class="(.*?)").*?(data-reoako-headword="(?<headword>.*?)").*?(data-reoako-id="(?<id>.*?)").*?(data-reoako-translation="(?<translation>.*?)").*?)>)(.*?)(<\/reoako>))/g;

      function ifShortcode(content) {
        return content.search(shortcode_regex) !== -1;
      }

      function replaceShortcodes(content) {
        const matches = [...content.matchAll(shortcode_regex)];
        matches.forEach((match) => {
          // Find all required attributes and rebuild tag
          if ("0" in match) {
            const str = match["0"];
            let headword,
              id,
              translation = undefined;
            if (match["groups"]["headword"]) {
              headword = match["groups"]["headword"];
            }
            if (match["groups"]["id"]) {
              id = match["groups"]["id"];
            }
            if (match["groups"]["translation"]) {
              translation = match["groups"]["translation"];
            }
            if (headword && id && translation) {
              const tag = `<reoako class="reoako-tinymce" data-reoako-headword="${headword}" data-reoako-id="${id}" data-reoako-translation="${translation}">${translation}</reoako>`;
              content = content.replace(str, tag);
            }
          }
        });
        return content;
      }

      editor.on("KeyUp", function(e){
        const evt = e;
        var charCode = evt.keyCode || evt.which;
        const currentNode = $(editor.selection.getEnd());
        const insideReoako = currentNode[0]?.nodeName === 'REOAKO';

        // 8 = backspace
        // 32 = space
        // 37 = left arrow
        // 39 = right arrow
        // 38 = up arrow
        // 40 = down arrow
        // 46 = delete

        // Ignore up or down
        if( [38, 40].includes(charCode)){
          return;
        }

        // auto select reoako word
        if(insideReoako && [37, 39].includes(charCode)){
          if(currentNode !== currentNode.closest("reoako")){
            const targetNode = currentNode.closest("reoako");
            const cursorPos = editor.selection.getRng().startOffset;
            const targetLength = currentNode[0]?.innerText.length;

            if(cursorPos !== targetLength){
              editor.selection.select(targetNode.get(0));
              evt.preventDefault();
              return;
            }
          }
        }

        // Delete the tag if you are inside it and press delete or backspace
        if(charCode === 8 && insideReoako || charCode === 46 && insideReoako){
          editor.execCommand('insertContent', true, ' ');
          currentNode.remove();
          evt.preventDefault();
          return;
        }

        if( ![37, 39].includes(charCode) && insideReoako){
          evt.preventDefault();
          return;
        }
    })
      function restoreShortcodes(content) {
        const matches = [...content.matchAll(reoako_regex)];

        matches.forEach((match) => {
          if ("0" in match) {
            const str = match["0"];
            let headword,
              id,
              translation = undefined;
            if (match["groups"]["headword"]) {
              headword = match["groups"]["headword"];
            }
            if (match["groups"]["id"]) {
              id = match["groups"]["id"];
            }
            if (match["groups"]["translation"]) {
              translation = match["groups"]["translation"];
            }
            if (headword && id && translation) {
              const tag = `[reoako data-reoako-headword="${headword}" data-reoako-id="${id}" data-reoako-translation="${translation}"]`;
              content = content.replace(str, tag);
            }
          }
        });
        return content;
      }

      editor.on("BeforeSetContent", function (event) {
        // No shortcodes in content, return.
        if (!ifShortcode(event.content)) {
          return;
        }
        event.content = replaceShortcodes(event.content);
      });

      editor.on("PostProcess", function (event) {
        if (event.get) {
          event.content = restoreShortcodes(event.content);
        }
      });

    });
  })(jQuery);
}.call(this));
