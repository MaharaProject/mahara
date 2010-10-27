function Slideshow(id, count) {
    this.id = '#slideshow' + id;
    this.count = count - 1;
    this.current = 0;
    this.advance = function() {
        if(this.current < this.count) {
            $j(this.id).height($j(this.id + " img:eq(" + this.current + ")").height());
            var self = this;
            $j(this.id + " img:eq(" + this.current + ")").fadeOut(500, function() {
                self.current++;
                $j(self.id + " img:eq(" + self.current + ")").fadeIn(500);
                $j(self.id).height($j(self.id + " img:eq(" + self.current + ")").height());
            });
        }
    }
    this.rewind = function() {
        if(this.current > 0) {
            $j(this.id).height($j(this.id + " img:eq(" + this.current + ")").height());
            var self = this;
            $j(this.id + " img:eq(" + this.current + ")").fadeOut(500, function() {
                self.current--;
                $j(self.id + " img:eq(" + self.current + ")").fadeIn(500);
                $j(self.id).height($j(self.id + " img:eq(" + self.current + ")").height());
            });
        }
    }
    $j(this.id + " img").hide();
    $j(this.id + " img:eq(" + this.current + ")").show();
}

