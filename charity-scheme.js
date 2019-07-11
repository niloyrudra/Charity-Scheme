
jQuery( document ).ready(
    function($) {
        var formsContainer = document.querySelector('.form-content');
        var formOne = document.querySelector('#charity-donation-form-one');
        var formOneSelect = document.querySelector('#charity-donation-form-one select');
        var formOneBtn = document.querySelector('#sub_btn');
        var csTypeField = document.querySelector('.cs-type-field');
        var formTwo;
        var form;

        // Charity Scheme Type Form One Exicution...
        if( formOne ) {
            formOne.addEventListener( 'submit', e => {
                e.preventDefault();
                csTypeField.innerText = '';
                
                var charityScheme = document.querySelector( '#charity_schemes' ).value,
                ajaxURL = formOne.getAttribute('data-url');
                // Field Validation...
                if( charityScheme === '' ) {
                    console.log( 'Required field is empty! Please Select an option.' );
                    csTypeField.innerText = 'You have to choose an option before proceeding!';
                    return;
                }
                // AJAX Call...
                $.ajax({
                    
                    url : ajaxURL,
                    type : 'post',
                    data : {
                        charityScheme : charityScheme,
                        action : 'save_charity_donation_form_one'
                    },
                    error : function( res ) {
                        console.log( res );
                    },
                    success : function( res ) {
                        if( res == 0 ) {
                            console.log( 'Unable to submit your donation plan! Please try again later.' );
                        } else {
                            console.log( 'Congratulations! Your donation plan has been successfully submitted.' );
                            
                            if( res ) {
                                var container = document.createElement( 'div' );
                                container.setAttribute( 'class', 'form-two-container' );
                                container.innerHTML = res;
                                // Appending Form Two...
                                formsContainer.appendChild(container);
                                // Disabled Form One
                                formOneSelect.setAttribute( 'disabled', 'disabled' );
                                formOneSelect.style.opacity = '0.5';
                                formOneBtn.setAttribute( 'disabled', 'disabled' );
                                formOneBtn.style.display = 'none';
                                formTwo = formOne.parentElement.nextElementSibling.firstChild;

                                // Charity Scheme Type Form Two Exicution...
                                if( formTwo !== '' ) {
                                    
                                    formTwo.addEventListener( 'submit', e => {
                                        e.preventDefault();
                                            
                                        var country = formTwo.querySelector( '#country' ).value,
                                            county = formTwo.querySelector( '#county' ).value,
                                            city = formTwo.querySelector( '#city' ).value,
                                            cptName = formTwo.querySelector( 'input[name="cpt_name"]' ).value,
                                            ajaxURL = formTwo.getAttribute('data-url'),
                                            institutionType;
                                                
                                            if( cptName === 'edu_institutions' ) {
                                                institutionType = formTwo.querySelector( '#edu_type' ).value;
                                            } else {
                                                institutionType = formTwo.querySelector( '#sport_type' ).value;
                                            }
                            
                                                console.log(ajaxURL);
                            
                                            // Field Validation...
                                            if( country === '' ) {
                                                formTwo.querySelector('.cs_country').innerText = 'Please to select a country!';
                                                return;
                                            }
                                            if( county === '' ) {
                                                formTwo.querySelector('.cs_county').innerText = 'Please select a county!';
                                                return;
                                            }
                                            if( city === '' ) {
                                                formTwo.querySelector('.cs_city').innerText = 'You have to select a city!';
                                                return;
                                            }
                                            if( institutionType === '' ) {
                                                if( cptName === 'edu_institutions' ) {
                                                    formTwo.querySelector('.cs_edu_type').innerText = 'You have to select a Institution!';
                                                    return;
                                                } else {
                                                    formTwo.querySelector('.cs_sport_type').innerText = 'You have to select a Club!';
                                                    return;
                                                }
                                                
                                            }

                                        // AJAX Call...
                                        $.ajax({
                            
                                            url : ajaxURL,
                                            type : 'post',
                                            data : {
                                                country : country,
                                                county : county,
                                                city : city,
                                                institutionType : institutionType,
                                                cptName : cptName,
                                                action : 'save_charity_donation_form_two'
                                            },
                                            error : function( res ) {
                                                 console.log( res );
                                            },
                                            success : function( res ) {
                                                if( res == 0 ) {
                                                    console.log( 'Unable to submit your donation plan! Please try again later.' );
                                                } else {
                                                    console.log( 'Congratulations! Your donation plan has been successfully submitted.' );
                            
                                                    if( res ) {
                                                            // Hidding Proceed Button
                                                        formTwo.querySelector('#option_btn').style.display = 'none';
                                                        var container = document.createElement( 'div' );
                                                            container.setAttribute( 'class', 'form-Three-container' );
                                                            container.innerHTML = res;
                                                        // Appending Form Two...
                                                        // formsContainer.innerHTML = '';
                                                        formsContainer.appendChild(container);

                                                        form = formOne.parentElement.nextElementSibling.nextElementSibling.firstChild.querySelector('#charity-donation-data-form');

                                                        // Final Form Exicution...
                                                        if( form ) {
                                                            
                                                            form.addEventListener( 'submit', (e) => {

                                                                e.preventDefault();
                                                                
                                                                var charityName = form.querySelector( '#donate_options' ).value,
                                                                    instituteType = form.querySelector( '#selected_type' ).value;
                                                                    postTypeID = form.querySelector( '#selected_cpt_name' ).value,
                                                                    donnerID = form.querySelector( '#cpt_user_id' ).value,
                                                                    country = form.querySelector( '#selected_country' ).value,
                                                                    county = form.querySelector( '#selected_county' ).value,
                                                                    city = form.querySelector( '#selected_city' ).value,
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
                                                                            formsContainer.innerHTML = '';
                                                                            formsContainer.innerHTML = '<h4 style="color:#54B948">Congratulations! Your donation plan has been successfully submitted.</h4>';
                                                                        }
                                                                    }

                                                                });
                                                                
                                                            } );

                                                        } // End IF Satement OF FINAL FORM

                                                    }
                                                        
                                                }
                                            }
                            
                                        });
                            
                                    } );

                                } // Form Two
                                                                    
                            }
                                
                        }

                    }
                        
                });
                    
            } );

        }
            
    }

);