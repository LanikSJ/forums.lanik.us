function showDrafts() {
    function escapeHtml(value) {
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;');
    }
    // Prepare the date formatter object
    var dateFormatter = new DateFormatter();
    dateFormatter.setLocaleSettings(pcgfLocales);
    // Generate a list of all saved drafts for the current forum and topic
    var draftListContent = '<div class="panel"><ul class="topiclist"><li class="header"><dl><dd>' + escapeHtml(pcgfLanguageDraftTime) + '</dd><dd>' + escapeHtml(pcgfLanguageSavedUntil) + '</dd><dd>' + escapeHtml(pcgfLanguageAutoDraft) + '</dd><dd>' + escapeHtml(pcgfLanguageDraftSubject) + '</dd><dd>' + escapeHtml(pcgfLanguageDraftMessage) + '</dd><dd>' + escapeHtml(pcgfLanguageAction) + '</dd></dl></li>';
    var drafts = getDrafts();
    var found = false;
    var draft;
    for (var i = 0; i < drafts.length; i++) {
        draft = drafts[i];
        if (draft.username === pcgfDraftUsername && draft.forumId === pcgfDraftForumId && draft.topicId === pcgfDraftTopicId) {
            found = true;
            draftListContent += '<li class="row"><dl><dd>' + escapeHtml(dateFormatter.format(pcgfDateTimeFormat, draft.time)) + '&nbsp;</dd><dd>' + (pcgfAutoDeleteInterval == 0 ? '' : escapeHtml(dateFormatter.format(pcgfDateTimeFormat, draft.time + (pcgfAutoDeleteInterval * 1)))) + '&nbsp;</dd><dd class="centered">' + escapeHtml(draft.autoDraft ? pcgfYes : pcgfNo) + '&nbsp;</dd><dd>' + escapeHtml(draft.subject) + '&nbsp;</dd><dd>' + escapeHtml(draft.message) + '&nbsp;</dd><dd><input id="pcgf-autodrafts-time" type="hidden" value="' + escapeHtml(draft.time) + '"/><input id="pcgf-autodrafts-load" class="button2" type="submit" value="' + escapeHtml(pcgfDraftLoadButton.val()) + '"/>&nbsp;<input id="pcgf-autodrafts-delete" class="button2" type="submit" value="' + escapeHtml(pcgfLanguageDeleteDraft) + '"/>&nbsp;</dd></dl></li>';
        }
    }
    if (!found) {
        draftListContent += '<li class="row not-found"><dl><dd>' + escapeHtml(pcgfLanguageNoDraftsFound) + '</dd></dl></li>';
    }
    draftListContent += '</ul></div>';
    // Show the draft list
    pcgfDraftList.html(draftListContent);
}