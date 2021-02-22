export const createdMixin = {
    created: function() {
        let self = this;

        window.addEventListener('click', function(e){
            // close dropdown when clicked outside
            if (!self.$el.contains(e.target)){
                self.all = false
            }
        })
    },

};
