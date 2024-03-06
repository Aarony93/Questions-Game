<script type="module">

    $(document).ready(function() {

        let toasrOptions = {
            "closeButton": true,
            "debug": false,
            "newestOnTop": false,
            "progressBar": true,
            "positionClass": "toast-top-right",
            "showDuration": "50000",
            "hideDuration": "50000",
            "timeOut": "50000",
            "extendedTimeOut": "50000",
            "showEasing": "swing",
            "hideEasing": "linear",
            "showMethod": "fadeIn",
            "hideMethod": "fadeOut",
        };


        // csrf token for all the ajax requests
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            }
        });

        const loadingHTML = '<div class="sk-chase">' +
            '<div class="sk-chase-dot"></div>' +
            '<div class="sk-chase-dot"></div>' +
            '<div class="sk-chase-dot"></div>' +
            '<div class="sk-chase-dot"></div>' +
            '<div class="sk-chase-dot"></div>' +
            '<div class="sk-chase-dot"></div>' +
        '</div>';

        function formLoading(action, division) {
            if (action == 'start') {
                if ($("div.sk-chase").length < 1) {
                    division.css({'opacity' : .3})
                    division.block({
                        message: loadingHTML,
                        css: {
                            border: 'none',
                            backgroundColor: 'transparent',
                            width: '5rem',
                        }
                    });
                }
            } else if (action == 'stop') {
                division.unblock();
                division.css({'opacity' : 1})
            } else if (action == 'half-block') {
                division.unblock();
                division.css({'opacity' : .5})
            }
        }

        const $formCheckInput = $(".form-check-input")

        $formCheckInput.on("click", function() {
            let $this = $(this)

            if ($this.is(":checked")) {

                $this.addClass('is-checked')

                let $cardBody = $(".card-body")
                formLoading('start', $(".card-body"), $cardBody);

                const path = "{{ route('check-answer') }}"

                let formData = new FormData();
                formData.append('game_id', $(".game-card").attr('id') );
                formData.append('question_id', $(".question-part").attr('id'));
                formData.append('answer_id', $this.parent().attr('data-answer-id'));

                $.ajax({
                    url: path,
                    type: 'POST',
                    contentType : false,
                    processData : false,
                    // cache : false,
                    data: formData,
                    success: function(data, textStatus, xhr) {
                    },
                    error: function(data) {

                        var response = data.responseJSON;
                        if (response != undefined && response != '') {
                            if (response.errors != undefined && response.errors != '') {
                                var errors = response.errors;
                                console.log("errors", errors)
                            }
                        }

                    },
                    complete: function(data, textStatus) {

                        if (data.status == 200) {
                            let response = data.responseJSON;

                            console.log("response", response)

                            toastr.options = toasrOptions

                            if (response.answer_status == 1) {
                                // correct

                                $formCheckInput.prop('disabled', true)
                                toastr.success(response.message);

                                if (response.multiple_answers_exist == 1) {

                                    $formCheckInput.prop('disabled', false)
                                    setTimeout(() => {
                                        $this.prop('disabled', true)
                                    }, 300);
                                }

                            } else {
                                // wrong

                                $formCheckInput.prop('disabled', true)
                                toastr.error(response.message);

                                if (response.correct_answer_ids) {

                                    let answerIds = response.correct_answer_ids
                                    for (let index = 0; index < answerIds.length; index++) {
                                        const correctAnswerID = answerIds[index];

                                        $('.card-body ul li').each(function (index, element) {
                                            let $lbl = $(this).find(".form-check label")
                                            let itemAnswerId = $lbl.attr('data-answer-id')

                                            if (itemAnswerId == correctAnswerID) {
                                                $lbl.find("h5").css({"color" : "green", "font-weight" : "bold"})
                                            }
                                            // console.log(element)
                                        });
                                    }
                                }

                                toastr.info(response.correct_answer_message)

                            }

                            toastr.info(response.multiple_answer_message);
                            $this.prop('disabled', true)


                            const $userPoints = $("#user-points")
                            let userPoints = $userPoints.text()
                            $userPoints.text( (+userPoints) + (+response.points))

                            let $nextQBtn = $("#next-q")
                            if (response.go_to_next_question_status == 1) {

                                $formCheckInput.prop('disabled', true)
                                $nextQBtn.removeClass('disabled')

                            } else {
                                $nextQBtn.addClass('disabled')
                            }

                            if (response.game_is_finished_status) {

                                if (response.game_points && response.game_points != 0) {
                                    toastr.success("Your score is "+response.game_points+" points, congratulations!");
                                }

                                toastr.warning("You will be redirected to home page in 20 seconds!");

                                setTimeout(() => {
                                    const pathHome = "{{ route('home') }}"
                                    window.location.replace(pathHome);
                                }, 20000);

                            }

                        }

                        setTimeout(() => {
                            formLoading('stop', $(".card-body"));
                        }, 1500);
                    }

                });

            }

        })





        calculateGameScore()

        function calculateGameScore() {
            const pathGetGameScore = "{{ route('get-game-score') }}"

            let formData = new FormData();
            formData.append('game_id', $(".game-card").attr('id') );

            $.ajax({
                url: pathGetGameScore,
                type: 'POST',
                contentType : false,
                processData : false,
                // cache : false,
                data: formData,
                success: function(data, textStatus, xhr) {
                },
                error: function(data) {

                    var response = data.responseJSON;
                    if (response != undefined && response != '') {
                        if (response.errors != undefined && response.errors != '') {
                            var errors = response.errors;
                            console.log("errors", errors)
                        }
                    }

                },
                complete: function(data, textStatus) {

                    if (data.status == 200) {
                        let response = data.responseJSON;

                        console.log("game score", response)

                        if (response.score) {
                            const $userPoints = $("#user-points")
                            // let userPoints = $userPoints.text()
                            $userPoints.text( +response.score)
                        }
                    }
                }

            });
        }

    })

</script>
