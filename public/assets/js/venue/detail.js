let getDataDetailVenue, populateDataDetail;

const currentUrl = location.pathname.split("/");
const venueCode = currentUrl[3];

getDataDetailVenue = () => {
    $.ajax({
        url: `/venue/detail/${venueCode}/data`,
        method: "GET",
        headers: {
            "X-Requested-With": "XMLHttpRequest",
        },
        success: function (res) {
            console.log(res);
            const dataDetailVenue = res.detailVenue;
            const dataField = res.dataField;

            populateDataDetail(dataDetailVenue, dataField);
        },
        error: function (xhr, status, error) {
            console.error("AJAX error:", error);
        },
    });
};

populateDataDetail = (dataDetailVenue, dataField) => {
    $("#venue_name").text(dataDetailVenue.name ?? "Venue name not found!");
    $("#venue_address").text(
        dataDetailVenue.address ?? "Venue address not found!"
    );
    $("#venue_type").text(
        dataDetailVenue.venue_type.name + " Venue" ?? "Venue type not found!"
    );
    $("#total_court").text(dataField.length ?? 0);
};

document.addEventListener("DOMContentLoaded", () => {
    getDataDetailVenue();
});
