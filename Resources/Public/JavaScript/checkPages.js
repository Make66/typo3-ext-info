console.log('checkFiles', checkFiles)
checkFiles.forEach((el) => {
    checkRemoteFile(el.site, el.url, 'robots', 'robots.txt');
    checkRemoteFile(el.site, el.url, 'sitemap', 'sitemap.xml');
    checkRemoteFile(el.site, el.url, '404', '404');
});

function checkRemoteFile(site, remoteUrl, type, file) {
    require(['TYPO3/CMS/Core/Ajax/AjaxRequest'], function (AjaxRequest) {
        new AjaxRequest(TYPO3.settings.ajaxUrls.sysinfo_checkpage)
            .withQueryArguments({
                file: remoteUrl + file,
                site: site,
                type: type,
            })
            .get()
            .then(async function (response) {
                const resolved = await response.resolve();
                //console.log(resolved.result);
                //console.log(resolved.result.type + '-' + resolved.result.site);
                setFileInfo(
                    resolved.result.type + '-' + resolved.result.site,
                    //typeof resolved.result !== 'undefined'
                    resolved.result.res
                );
            });
    });
}
function setFileInfo(idStr, fileExists) {
    console.log(idStr);
    const iconGranted =
        '<span class="t3js-icon icon icon-size-small icon-state-default icon-status-status-permission-granted" data-identifier="status-status-permission-granted">\n' +
        '<span class="icon-markup"><svg class="icon-color" style="color: green">' +
        '<use xlink:href="/typo3/sysext/core/Resources/Public/Icons/T3Icons/sprites/actions.svg#actions-check"></use></svg></span></span>';
    const iconDenied =
        '<span class="t3js-icon icon icon-size-small icon-state-default icon-status-status-permission-denied" data-identifier="status-status-permission-denied">\n' +
        '<span class="icon-markup"><svg class="icon-color"  style="color: red">' +
        '<use xlink:href="/typo3/sysext/core/Resources/Public/Icons/T3Icons/sprites/actions.svg#actions-close"></use></svg></span></span>';

    document.getElementById(idStr).innerHTML = (fileExists)
        ? iconGranted
        : iconDenied;
}