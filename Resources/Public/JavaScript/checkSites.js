/*  HTML contains:
    var sites = [
        { site:"bbw", url:"https://www.baukultur-bw.de/" },
    ];
 */
import "bootstrap";
import AjaxRequest from "@typo3/core/ajax/ajax-request.js";

class SysinfoCheck {

    constructor() {
        this.ready = !0, this.options = {
            dada: "test",
        },
        sites.forEach((el) => {
            this.checkRemoteFile(el.site, el.url, 'robots', 'robots.txt');
            this.checkRemoteFile(el.site, el.url, 'sitemap', 'sitemap.xml');
            this.checkRemoteFile(el.site, el.url, '404', '404');
            this.checkRemoteFile(el.site, el.url, '404html', '404.html');
        });
    }

    checkRemoteFile(site, remoteUrl, type, file) {
        new AjaxRequest(TYPO3.settings.ajaxUrls.sysinfo_curl)
                .withQueryArguments({
                    file: remoteUrl + file,
                    site: site,
                    type: type,
                })
                .get()
                .then(async function(response) {
                    const resolved = await response.resolve("application/json")
                    const idStr = resolved.result.type + '-' + resolved.result.site
                    const fileExists = resolved.result.res
                    console.log('resolved', resolved)
                    document.getElementById(idStr).innerHTML = (fileExists)
                        // iconGranted
                        ? '<span class="icon icon-size-small icon-state-default icon-status-status-permission-granted" data-identifier="status-status-permission-granted">\n' +
                          '<span class="icon-markup"><svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" viewBox="0 0 16 16">' +
                          '<g fill="currentColor"><path d="m13.3 4.8-.7-.7c-.2-.2-.5-.2-.7 0L6.5 9.5 4 6.9c-.2-.2-.5-.2-.7 0l-.6.7c-.2.2-.2.5 0 .7l3.6 3.6c.2.2.5.2.7 0l6.4-6.4c.1-.2.1-.5-.1-.7z"/></g>' +
                          '</svg></span></span>'
                        // iconDenied
                        : '<span class="icon icon-size-small icon-state-default icon-status-status-permission-denied" data-identifier="status-status-permission-denied">\n' +
                          '<span class="icon-markup"><svg xmlns="http://www.w3.org/2000/svg" xml:space="preserve" viewBox="0 0 16 16">' +
                          '<g fill="currentColor"><path d="M11.9 5.5 9.4 8l2.5 2.5c.2.2.2.5 0 .7l-.7.7c-.2.2-.5.2-.7 0L8 9.4l-2.5 2.5c-.2.2-.5.2-.7 0l-.7-.7c-.2-.2-.2-.5 0-.7L6.6 8 4.1 5.5c-.2-.2-.2-.5 0-.7l.7-.7c.2-.2.5-.2.7 0L8 6.6l2.5-2.5c.2-.2.5-.2.7 0l.7.7c.2.2.2.5 0 .7z"/></g>' +
                          '</svg></span></span>'

                })
    }
}
export default new SysinfoCheck;
