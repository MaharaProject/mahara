function Slideshow(id, count) {
    var self = this;
    this.id = '#slideshow' + id;
    this.count = count - 1;
    this.current = 0;
    this.change = function(to) {
        if (to == this.current || to < 0 || to > this.count) {
            return false;
        }
        $j(this.id).height($j(this.id + " img:eq(" + this.current + ")").height());
        $j(this.id + " img:eq(" + this.current + ")").fadeOut(500, function() {
            self.current = to;
            $j(self.id + " img:eq(" + self.current + ")").fadeIn(500);
            $j(self.id).height($j(self.id + " img:eq(" + self.current + ")").height());
        });
        $j(this.id + ' td.control span').removeClass('disabled');
        $j(this.id + ' td.control span.first').addClass('hidden');
        if (to == 0) {
            $j(this.id + ' td.control span.prev').addClass('disabled');
        }
        else if (to == this.count) {
            $j(this.id + ' td.control span.next').addClass('disabled');
            $j(this.id + ' td.control span.first').removeClass('hidden');
        }
        return false;
    }
    $j(this.id + ' td.control span.next').click(function() {return self.change(self.current + 1);});
    $j(this.id + ' td.control span.prev').click(function() {return self.change(self.current - 1);});
    $j(this.id + ' td.control span.first').click(function() {return self.change(0);});
    // $j(this.id + ' td.control span.last').click(function() {return self.change(self.count);});
    $j(this.id + " img").hide();
    $j(this.id + " img:eq(" + this.current + ")").show();
    $j(this.id + ' td.control span.next').removeClass('disabled');
}

