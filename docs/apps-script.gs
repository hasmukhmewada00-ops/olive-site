/**
 * Olive Catering: enquiry log (Google Apps Script)
 *
 * Setup (in the client's common Gmail):
 * 1. Create a Google Sheet named "Olive Website Enquiries".
 * 2. Extensions > Apps Script. Paste this file. Set SECRET below to a random string.
 * 3. Deploy > New deployment > Web app.
 *      Execute as: Me
 *      Who has access: Anyone
 *    Copy the Web app URL.
 * 4. In config.php on the server set:
 *      'sheet_webhook_url'    => '<Web app URL>',
 *      'sheet_webhook_secret' => '<same SECRET>',
 *
 * The site only writes rows. The "status" column is for the client to update
 * (New / Called / Quoted / Won / Lost).
 */

var SECRET = 'CHANGE-ME-TO-A-LONG-RANDOM-STRING';
var SHEET_NAME = 'Leads';

function doPost(e) {
  var lock = LockService.getScriptLock();
  lock.waitLock(10000);
  try {
    var body = JSON.parse(e.postData.contents || '{}');
    if (body.secret !== SECRET) {
      return json_({ ok: false, error: 'unauthorised' });
    }
    var columns = body.columns || [];
    var lead = body.lead || {};

    var ss = SpreadsheetApp.getActiveSpreadsheet();
    var sheet = ss.getSheetByName(SHEET_NAME) || ss.insertSheet(SHEET_NAME);
    if (sheet.getLastRow() === 0) {
      sheet.appendRow(columns);
      sheet.setFrozenRows(1);
    }

    var row = columns.map(function (c) {
      var v = lead[c] == null ? '' : String(lead[c]);
      return /^[=+\-@]/.test(v) ? "'" + v : v; // never let a cell run as a formula
    });
    sheet.appendRow(row);
    return json_({ ok: true });
  } catch (err) {
    return json_({ ok: false, error: String(err) });
  } finally {
    lock.releaseLock();
  }
}

function json_(obj) {
  return ContentService.createTextOutput(JSON.stringify(obj)).setMimeType(ContentService.MimeType.JSON);
}
