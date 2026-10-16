function showDrafts() {
    // Prepare the date formatter object
    var dateFormatter = new DateFormatter();
    dateFormatter.setLocaleSettings(pcgfLocales);
    // Generate a list of all saved drafts for the current forum and topic
    var draftTable = $('<table class="pcgf-drafts"></table>');
    var headerRow = $('<tr class="centered panel-heading"></tr>');
    headerRow
        .append($('<th></th>').text(pcgfLanguageDraftTime))
        .append($('<th></th>').text(pcgfLanguageSavedUntil))
        .append($('<th></th>').text(pcgfLanguageAutoDraft))
        .append($('<th></th>').text(pcgfLanguageDraftSubject))
        .append($('<th></th>').text(pcgfLanguageDraftMessage))
        .append($('<th></th>').text(pcgfLanguageAction));
    draftTable.append(headerRow);

    var drafts = getDrafts();
    var found = false;
    var draft;
    for (var i = 0; i < drafts.length; i++) {
        draft = drafts[i];
        if (draft.username === pcgfDraftUsername && draft.forumId === pcgfDraftForumId && draft.topicId === pcgfDraftTopicId) {
            found = true;
            var row = $('<tr></tr>');
            row.append($('<td></td>').text(dateFormatter.format(pcgfDateTimeFormat, draft.time)));
            row.append($('<td></td>').text(pcgfAutoDeleteInterval == 0 ? '' : dateFormatter.format(pcgfDateTimeFormat, draft.time + (pcgfAutoDeleteInterval * 1))));
            row.append($('<td class="centered"></td>').text(draft.autoDraft ? pcgfYes : pcgfNo));
            row.append($('<td></td>').text(draft.subject));
            row.append($('<td></td>').text(draft.message));

            var actionsCell = $('<td></td>');
            actionsCell.append($('<input id="pcgf-autodrafts-time" type="hidden"/>').val(draft.time));
            actionsCell.append($('<input id="pcgf-autodrafts-load" class="button2" type="submit"/>').val(pcgfDraftLoadButton.val()));
            actionsCell.append($('<input id="pcgf-autodrafts-delete" class="button2" type="submit"/>').val(pcgfLanguageDeleteDraft));
            row.append(actionsCell);

            draftTable.append(row);
        }
    }
    if (!found) {
        draftTable.append(
            $('<tr></tr>').append(
                $('<td colspan="6"></td>').text(pcgfLanguageNoDraftsFound)
            )
        );
    }
    // Show the draft list
    pcgfDraftList.empty().append(draftTable);
}