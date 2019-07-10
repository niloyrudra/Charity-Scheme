jQuery( document ).ready(
    function($) {
        // var form = document.querySelector('.charity-scheme-form-content form');

        var form = document.querySelector('#charity-donation-data-form');
        
        if( form ) {
            
            form.addEventListener( 'submit', (e) => {
                e.preventDefault();
                
                var charityName = document.querySelector( '#donate_options' ).value,
                    instituteType = document.querySelector( '#selected_type' ).value;
                    postTypeID = document.querySelector( '#selected_cpt_name' ).value,
                    donnerID = document.querySelector( '#cpt_user_id' ).value,
                    country = document.querySelector( '#selected_country' ).value,
                    county = document.querySelector( '#selected_county' ).value,
                    city = document.querySelector( '#selected_city' ).value,
                    ajaxURL = form.getAttribute('data-url');

                if( charityName == '' ) {
                    console.log( 'Required option is empty!' ); // Needs to modify
                    return;
                }

                $.ajax({

                    url : ajaxURL,
                    type : 'post',
                    data : {
                        name : charityName,
                        instituteType : instituteType,
                        postTypeID : postTypeID,
                        donnerID : donnerID,
                        country : country,
                        county : county,
                        city : city,
                        action : 'save_charity_donation_data_form'
                    },
                    error : function( res ) {
                        console.log( res );
                    },
                    success : function( res ) {
                        if( res == 0 ) {
                            console.log( 'Unable to submit your donation plan! Please try again later.' );
                        } else {
                            console.log( 'Congratulations! Your donation plan has been successfully submitted.' );
                        }
                    }

                });
                
            } );

        } // End IF Satement

    }
);