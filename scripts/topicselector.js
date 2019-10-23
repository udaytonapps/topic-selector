/*Main Javascript File*/
$(document).ready(function(){
    $('.results-collapse.collapse').on('show.bs.collapse', function(){
        let rowDiv = $(this).parent();
        rowDiv.find(".fa.rotate").addClass("open");
        rowDiv.parent().addClass("selected-row");
    }).on('hide.bs.collapse', function(){
        let rowDiv = $(this).parent();
        rowDiv.find(".fa.rotate").removeClass("open");
        rowDiv.parent().removeClass("selected-row");
    });

    $("#importModal").on("hidden.bs.modal", function() {
        $(this).find('.results-collapse.collapse').collapse("hide");
        $(this).find("input[name='topic']").prop("checked", false);
    });
});
function confirmDeleteTopic() {
    return confirm("Are you sure you want to delete this topic? This action cannot be undone.");
}
function confirmDeleteTopicBlank(topicId) {
    if ($("#topicTextInput"+topicId).val().trim().length < 1) {
        return confirm("Saving this topic with blank text will delete this topic. Are you sure you want to delete this topic? This action cannot be undone.");
    } else {
        return true;
    }
}
function showNewTopicRow() {
    let addTopicsSection = $("#addTopics");
    let topicRow = $("#newTopicRow");

    addTopicsSection.hide();
    topicRow.fadeIn();
    let theForm = $("#topicTextForm-1");
    theForm.find('#topicTextInput-1').focus()
        .off("keypress").on("keypress", function(e) {
            if(e.which === 13) {
                e.preventDefault();
                $.ajax({
                    type: "POST",
                    dataType: "json",
                    url: theForm.prop("action"),
                    data: theForm.serialize(),
                    success: function(data) {
                        $("#newTopicRow").before(data.new_topic);
                        $("#topicTextInput-1").val('');
                        let nextNumber = $(".topic-number").last().parent().data("topic-number") + 1;
                        topicRow.data("topic-number", nextNumber);
                        $("#flashmessages").html(data.flashmessage);
                        setupAlertHide();
                        topicRow.hide();
                        addTopicsSection.show();
                    }
                });
            }
        });
    theForm.find('#topicStuAllowed-1').off("keypress").on("keypress", function(e) {
        if(e.which === 13) {
            e.preventDefault();
            $.ajax({
                type: "POST",
                dataType: "json",
                url: theForm.prop("action"),
                data: theForm.serialize(),
                success: function(data) {
                    $("#newTopicRow").before(data.new_topic);
                    $("#topicTextInput-1").val('');
                    let nextNumber = $(".topic-number").last().parent().data("topic-number") + 1;
                    topicRow.data("topic-number", nextNumber);
                    $("#flashmessages").html(data.flashmessage);
                    setupAlertHide();
                    topicRow.hide();
                    addTopicsSection.show();
                }
            });
        }
    });
    $("#topicSaveAction-1").off("click").on("click", function(e) {
        $.ajax({
            type: "POST",
            dataType: "json",
            url: theForm.prop("action"),
            data: theForm.serialize(),
            success: function(data) {
                $("#newTopicRow").before(data.new_topic);
                $("#topicTextInput-1").val('');
                let nextNumber = $(".topic-number").last().parent().data("topic-number") + 1;
                topicRow.data("topic-number", nextNumber);
                $("#flashmessages").html(data.flashmessage);
                setupAlertHide();
                topicRow.hide();
                addTopicsSection.show();
            }
        });
    });
    $("#topicCancelAction-1").off("click").on("click", function(e) {
        $("#topicTextInput-1").val('');
        topicRow.hide();
        addTopicsSection.show();
    });
}
function editTopicText(topicId) {
    let topicText =$("#topicText"+topicId);
    topicText.hide();
    $("#topicDeleteAction"+topicId).hide();
    $("#topicEditAction"+topicId).hide();
    $("#topicReorderAction"+topicId).hide();

    let theForm = $("#topicTextForm"+topicId);

    theForm.show();
    theForm.find('#topicTextInput'+topicId).focus()
        .off("keypress").on("keypress", function(e) {
            if(e.which === 13) {
                e.preventDefault();
                if ($('#topicTextInput'+topicId).val().trim() === '') {
                    if(confirmDeleteTopicBlank(topicId)) {
                        // User entered blank topic text and wants to delete.
                        deleteTopic(topicId, true);
                    }
                } else {
                    // Still has text in topic. Save it.
                    $.ajax({
                        type: "POST",
                        url: theForm.prop("action"),
                        data: theForm.serialize(),
                        success: function(data) {
                            topicText.find(".topic-title").text($('#topicTextInput'+topicId).val());
                            topicText.find(".topic-slots").text($('#topicStuAllowed'+topicId).val());
                            topicText.show();
                            $("#topicDeleteAction"+topicId).show();
                            $("#topicEditAction"+topicId).show();
                            $("#topicReorderAction"+topicId).show();
                            $("#topicSaveAction"+topicId).hide();
                            $("#topicCancelAction"+topicId).hide();
                            theForm.hide();
                            $("#flashmessages").html(data.flashmessage);
                            setupAlertHide();
                        }
                    });
                }
            }
        });
    theForm.find('#topicStuAllowed'+topicId).off("keypress").on("keypress", function(e) {
        if(e.which === 13) {
            e.preventDefault();
            if ($('#topicTextInput'+topicId).val().trim() === '') {
                if(confirmDeleteTopicBlank(topicId)) {
                    // User entered blank topic text and wants to delete.
                    deleteTopic(topicId, true);
                }
            } else {
                // Still has text in topic. Save it.
                $.ajax({
                    type: "POST",
                    url: theForm.prop("action"),
                    data: theForm.serialize(),
                    success: function(data) {
                        topicText.find(".topic-title").text($('#topicTextInput'+topicId).val());
                        topicText.find(".topic-slots").text($('#topicStuAllowed'+topicId).val());
                        topicText.show();
                        $("#topicDeleteAction"+topicId).show();
                        $("#topicEditAction"+topicId).show();
                        $("#topicReorderAction"+topicId).show();
                        $("#topicSaveAction"+topicId).hide();
                        $("#topicCancelAction"+topicId).hide();
                        theForm.hide();
                        $("#flashmessages").html(data.flashmessage);
                        setupAlertHide();
                    }
                });
            }
        }
    });
    $("#topicSaveAction"+topicId).show()
        .off("click").on("click", function(e) {
            if ($('#topicTextInput'+topicId).val().trim() === '') {
                if(confirmDeleteTopicBlank(topicId)) {
                    // User entered blank topic text and wants to delete.
                    deleteTopic(topicId, true);
                }
            } else {
                // Still has text in topic. Save it.
                $.ajax({
                    type: "POST",
                    url: theForm.prop("action"),
                    data: theForm.serialize(),
                    success: function(data) {
                        topicText.find(".topic-title").text($('#topicTextInput'+topicId).val());
                        topicText.find(".topic-slots").text($('#topicStuAllowed'+topicId).val());
                        topicText.show();
                        $("#topicDeleteAction"+topicId).show();
                        $("#topicEditAction"+topicId).show();
                        $("#topicReorderAction"+topicId).show();
                        $("#topicSaveAction"+topicId).hide();
                        $("#topicCancelAction"+topicId).hide();
                        theForm.hide();
                        $("#flashmessages").html(data.flashmessage);
                        setupAlertHide();
                    }
                });
            }
    });

    $("#topicCancelAction"+topicId).show()
        .off("click").on("click", function(e) {
        let theText = $("#topicText"+topicId);
        theText.show();
        theForm.hide();
        $("#topicTextInput"+topicId).val(topicText.find(".topic-title").text());
        $("#topicStuAllowed"+topicId).val(topicText.find(".topic-slots").text());
        $("#topicDeleteAction"+topicId).show();
        $("#topicEditAction"+topicId).show();
        $("#topicReorderAction"+topicId).show();
        $("#topicSaveAction"+topicId).hide();
        $("#topicCancelAction"+topicId).hide();
    });
}
function moveTopicUp(topicId) {
    $.ajax({
        type: "POST",
        dataType: "json",
        url: "actions/ReorderTopic.php?PHPSESSID=" + $("#sess").val(),
        data: {
            "topic_id": topicId
        },
        success: function(data) {
            let theTopicMoved = $("#topicRow" + topicId);
            theTopicMoved.hide();
            let currentNumber = theTopicMoved.data("topic-number");
            if (currentNumber === 1) {
                // Move to bottom
                $("#newTopicRow").before(theTopicMoved);
            } else {
                // Move up one
                theTopicMoved.prev().before(theTopicMoved);
            }
            // Fix up topic numbers
            let topicNum = 1;
            $(".topic-row").each(function() {
                $(this).data("topic-number", topicNum);
                topicNum++;
            });

            theTopicMoved.fadeIn("fast");

            $("#flashmessages").html(data.flashmessage);
            setupAlertHide();
        }
    });
}
function deleteTopic(topicId, skipconfirm = false) {
    if (skipconfirm || confirmDeleteTopic()) {
        $.ajax({
            type: "POST",
            dataType: "json",
            url: "actions/DeleteTopic.php?PHPSESSID=" + $("#sess").val(),
            data: {
                "topic_id": topicId
            },
            success: function(data) {
                $("#topicRow" + topicId).remove();
                // Fix up topic numbers
                let topicNum = 1;
                $(".topic-number").each(function() {
                    $(this).text(topicNum + ".");
                    $(this).parent().data("topic-number", topicNum);
                    topicNum++;
                });
                // Fix new topic number
                $("#newTopicRow").data("topic-number", topicNum);
                $("#newTopicNumber").text(topicNum + ".");

                $("#flashmessages").html(data.flashmessage);
                setupAlertHide();
            }
        });
    }
}
function setupAlertHide() {
    // On load hide any alerts after 3 seconds
    setTimeout(function() {
        $(".alert-banner").slideUp();
    }, 3000);
}
