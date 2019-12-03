(function( $ ) {

  var colorPalette = ['000000', 'FF9966', '6699FF', '99FF66', 'CC0000', '00CC00', '0000CC', '333333', '0066FF', 'FFFFFF'];

  $('.wysiwyg').each(function (index) {
    $(this).append(`<div class="toolbar">
      <button type="button" data-command='undo'><i class="fas fa-undo"></i></button>
      <button type="button" data-command='redo'><i class='fas fa-redo'></i></button>
      <button type="button" class="fore-wrapper"><i class='fas fa-font' style='color:#c96;'></i>
        <div class="fore-palette">
        </div>
      </button>
      <button type="button" data-command='bold'><i class='fas fa-bold'></i></button>
      <button type="button" data-command='italic'><i class='fas fa-italic'></i></button>
      <button type="button" data-command='underline'><i class='fas fa-underline'></i></button>
      <button type="button" data-command='strikeThrough'><i class='fas fa-strikethrough'></i></button>
      <button type="button" data-command='justifyLeft'><i class='fas fa-align-left'></i></button>
      <button type="button" data-command='justifyCenter'><i class='fas fa-align-center'></i></button>
      <button type="button" data-command='justifyRight'><i class='fas fa-align-right'></i></button>
      <button type="button" data-command='justifyFull'><i class='fas fa-align-justify'></i></button>
      <button type="button" data-command='insertUnorderedList'><i class='fas fa-list-ul'></i></button>
      <button type="button" data-command='insertOrderedList'><i class='fas fa-list-ol'></i></button>
      <button type="button" data-command='h1'>H1</button>
      <button type="button" data-command='h2'>H2</button>
      <button type="button" data-command='createlink'><i class='fas fa-link'></i></button>
      <button type="button" data-command='unlink'><i class='fas fa-unlink'></i></button>
    </div>`);
    $(this).append(`<div class="editor" contenteditable></div>`);

    for (var i = 0; i < colorPalette.length; i++) {
      $(this).find('.fore-palette').append('<button type="button" data-command="foreColor" data-value="' + '#' + colorPalette[i] + '" style="background-color:' + '#' + colorPalette[i] + ';" class="palette-item"></button>');
    }

    document.execCommand('styleWithCSS', false, true);

    $(this).find('.toolbar button').click(function(e) {
      var command = $(this).data('command');
      if (command == 'h1' || command == 'h2') {
        document.execCommand('formatBlock', false, command);
      } else if (command == 'foreColor') {
        document.execCommand($(this).data('command'), false, $(this).data('value'));
      } else if (command == 'createlink') {
        url = prompt('Enter the link here: ', 'http:\/\/');
        document.execCommand($(this).data('command'), false, url);
      } else
        document.execCommand($(this).data('command'), false, null);
    });
  });

}( jQuery ));
