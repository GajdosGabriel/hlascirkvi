import moment from "moment";

export const filterMixin = {
    filters: {
        dateTime: function (value) {
            return moment(value).format('D.M.Y, H:mm');
        }
    }

};
