function Paginator(e) {
  this.element = e;
  this.page = 0;
  this.pageSize = 0;
  this.total = 0;

  var onPaginateChange = null;

  this.paginate = function(page, pageSize, total) {
    this.page = page;
    this.pageSize = pageSize;
    this.total = total;

    var controller = '';
    var totalPage = Math.ceil(this.total / this.pageSize);

    if (+this.page > 1) {
      if (+this.page == 2)
        controller = controller + "<a pn='" + (+this.page - 1) + "' class='btn btn-primary'>Previous</a>&nbsp;&nbsp;&nbsp;";
      else {
        controller = controller + "<a pn='";
        controller = controller + (+this.page - 1) + "' class='btn btn-primary'>Previous</a>&nbsp;&nbsp;&nbsp;";
      }
    }
    else
      controller = controller + "<span class='btn btn-primary disabled'>Previous</span>&nbsp;&nbsp;&nbsp;";
    if ((+this.page - 3) > 1)
      controller = controller + "<a pn='1' class='btn btn-primary'>1</a>&nbsp;.....&nbsp;|&nbsp;";
    for (var i = +this.page - 3; i <= +this.page; i++) {
      if (i >= 1) {
        if (+this.page != i) {
          controller = controller + "<a pn='";
          controller = controller + i + "' class='btn btn-primary'>" + i + "</a>&nbsp;|&nbsp;";
        }
        else {
          controller = controller + "<span style='font-weight:bold;'>" + i + "</span>&nbsp;|&nbsp;";
        }
      }
    }
    for (var i = +this.page + 1; i <= +this.page + 3; i++) {
      if (i <= totalPage) {
        if (+this.page != i) {
          controller = controller + "<a pn='";
          controller = controller + i + "' class='btn btn-primary'>" + i + "</a>&nbsp;|&nbsp;";
        }
        else {
          controller = controller + "<span style='font-weight:bold;'>" + i + "</span>&nbsp;|&nbsp;";
        }
      }
    }
    if ((+this.page + 3) < totalPage) {
      controller = controller + ".....&nbsp;<a pn='";
      controller = controller + totalPage + "' class='btn btn-primary'>" + totalPage + "</a>";
    }
    if (+this.page < totalPage) {
      controller = controller + "&nbsp;&nbsp;&nbsp;<a pn='";
      controller = controller + (+this.page + 1) + "' class='btn btn-primary'>Next</a>";
    }
    else
      controller = controller + "&nbsp;&nbsp;&nbsp;<span class='btn btn-primary disabled'>Next</span>";

    $(this.element).html(controller);
    if (this.onPaginateChange !== null) {
      var that = this;

      $(this.element).unbind().on("click", "a", function() {
        var askPage = $(this).attr('pn');
        that.onPaginateChange(askPage);
      });
    }
  };

  this.setOnPaginateChange = function(f) {
    this.onPaginateChange = f;
  };
};
