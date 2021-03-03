import moment from "moment";

export const filterMixin = {
    filters: {
        dateTime: function (value) {
            return moment(value).format('D.M.Y, H:mm');
        },
        humanDateTime: function (value) {
            return moment(value).locale("sk").startOf('hour').fromNow();
            // return moment(value).locale("sk").format('D.M.Y, H:mm');
        }
    }

};
