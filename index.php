<!DOCTYPE html>
<html>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1" />

<head>
    <title>CubicleSoft File Explorer Demo</title>
</head>

<body>
    <style type="text/css">
        body {
            font-family: Verdana, Arial, Helvetica, sans-serif;
            position: relative;
            color: #222222;
            font-size: 1.0em;
        }

        html.embed,
        html.embed body {
            padding: 0;
            margin: 0;
        }

        html.embed p {
            padding: 0;
            margin: 0;
            display: none;
        }

        #filemanager {
            height: 50vh;
            max-height: 400px;
            position: relative;
        }

        html.embed #filemanager {
            height: 100vh;
        }
    </style>

    <link rel="stylesheet" type="text/css" href="file-explorer/file-explorer.css">
    <script type="text/javascript" src="file-explorer/file-explorer.js"></script>

    <div id="filemanager"></div>

    <script type="text/javascript">
        (function() {
            // Handle iframe demo embed.
            if (window.location.href.indexOf('embed=true') > -1) document.documentElement.classList.add('embed');

            var url = '/hello-library/js-fileexplorer/xhr.php';
            var token = 'asdfasdf';
            var fe = new window.FileExplorer(document.getElementById('filemanager'), {
                // This allows drag-and-drop and cut/copy/paste to work between windows of the live demo.
                // Your application should either define the group uniquely for your application or not at all.
                group: 'demo_test_group',
                capturebrowser: false,
                initpath: [
                    ['', 'Folders', {
                        canmodify: true
                    }],
                ],
                onfocus: function(e) {
                    console.log('focus');
                    console.log(e);
                },
                onblur: function(e) {
                    console.log('blur');
                    console.log(e);
                },
                // // See main documentation for the complete list of keys.
                // // The only tool that won't show as a result of a handler being defined is 'item_checkboxes'.
                // tools: {
                //     item_checkboxes: true
                // },

                onrefresh: function(folder, required) {
                    // Optional:  Ignore non-required refresh requests.  By default, folders are refreshed every 5 minutes so the widget has up-to-date information.
                    // if (!required) return;

                    // Maybe notify a connected WebSocket here to watch the folder on the server for changes.
                    if (folder === this.GetCurrentFolder()) {
                        var $this = this;

                        var xhr = new this.PrepareXHR({
                            url: url,
                            params: {
                                action: 'file_explorer_refresh',
                                path: JSON.stringify(folder.GetPathIDs()),
                                xsrftoken: token
                            },
                            onsuccess: function(e) {
                                var data = JSON.parse(e.target.response);
                                if (data.success) folder.SetEntries(data.entries);
                                else if (required) $this.SetNamedStatusBarText('folder', $this.EscapeHTML('Failed to load folder.  ' + data.error));
                            },
                            onerror: function(e) {
                                // Maybe output a nice message if the request fails for some reason.
                                //					if (required)  $this.SetNamedStatusBarText('folder', 'Failed to load folder.  Server error.');

                                console.log(e);
                            }
                        });

                        xhr.Send();
                    }
                },

                // Note:  'entry' is a copy of the original, so it is okay to modify any aspect of it, including 'id'.
                onrename: function(renamed, folder, entry, newname) {
                    var xhr = new this.PrepareXHR({
                        url: url,
                        params: {
                            action: 'file_explorer_rename',
                            path: JSON.stringify(folder.GetPathIDs()),
                            id: entry.id,
                            newname: newname,
                            xsrftoken: token
                        },
                        onsuccess: function(e) {
                            var data = JSON.parse(e.target.response);
                            // Updating the existing entry or passing in a completely new entry to the renamed() callback are okay.
                            if (data.success) renamed(data.entry);
                            else renamed(data.error);
                        },
                        onerror: function(e) {
                            console.log(e);
                            renamed('Server/network error.');
                        }
                    });

                    xhr.Send();
                },

                onnewfolder: function(created, folder) {
                    var xhr = new this.PrepareXHR({
                        url: url,
                        params: {
                            action: 'file_explorer_new_folder',
                            path: JSON.stringify(folder.GetPathIDs()),
                            xsrftoken: token
                        },
                        onsuccess: function(e) {
                            var data = JSON.parse(e.target.response);
                            if (data.success) created(data.entry);
                            else created(data.error);
                        },
                        onerror: function(e) {
                            console.log(e);
                        }
                    });

                    xhr.Send();
                },

                onopenfile: function(folder, entry) {
                    console.log('onopenfile');
                    console.log(entry);
                },

                // onnewfile: function(created, folder) {
                //     var xhr = new this.PrepareXHR({
                //         url: url,
                //         params: {
                //             action: 'file_explorer_new_file',
                //             path: JSON.stringify(folder.GetPathIDs()),
                //             xsrftoken: token
                //         },
                //         onsuccess: function(e) {
                //             var data = JSON.parse(e.target.response);
                //             if (data.success) created(data.entry);
                //             else created(data.error);
                //         },
                //         onerror: function(e) {
                //             console.log(e);
                //         }
                //     });

                //     xhr.Send();
                // },

                oninitupload: function(startupload, fileinfo, queuestarted) {
                    console.log('---------> oninitupload');
                    // console.log(fileinfo);
                    // console.log(JSON.stringify(fileinfo.folder.GetPathIDs()));

                    if (fileinfo.type === 'dir') {
                        var $this = this;
                        var pathArray = fileinfo.fullPath.split('/');
                        var xhr = new this.PrepareXHR({
                            url: url,
                            params: {
                                action: 'file_explorer_new_folder',
                                path: JSON.stringify(fileinfo.folder.GetPathIDs()),
                                xsrftoken: token,
                                name: pathArray[pathArray.length - 1],
                            },
                            onsuccess: function(e) {
                                var xhr2 = new $this.PrepareXHR({
                                    url: url,
                                    params: {
                                        action: 'file_explorer_refresh',
                                        path: JSON.stringify(fileinfo.folder.GetPathIDs()),
                                        xsrftoken: token
                                    },
                                    onsuccess: function(e) {
                                        var data = JSON.parse(e.target.response);
                                        if (data.success) fileinfo.folder.SetEntries(data.entries);
                                        else if (required) $this.SetNamedStatusBarText('folder', $this.EscapeHTML('Failed to load folder.  ' + data.error));
                                    },
                                    onerror: function(e) {
                                        // Maybe output a nice message if the request fails for some reason.
                                        //					if (required)  $this.SetNamedStatusBarText('folder', 'Failed to load folder.  Server error.');

                                        console.log(e);
                                    }
                                });

                                xhr2.Send();
                            },
                            onerror: function(e) {
                                console.log(e);
                            }
                        });

                        xhr.Send();
                    } else {
                        fileinfo.url = url;
                        fileinfo.headers = {};
                        fileinfo.params = {
                            action: 'file_explorer_upload',
                            id: Date.now(),
                            path: JSON.stringify(fileinfo.folder.GetPathIDs()),
                            name: fileinfo.fullPath,
                            size: fileinfo.file.size,
                            xsrftoken: token,
                            queuestarted: queuestarted,
                        };
                        fileinfo.fileparam = 'file';
                        fileinfo.chunksize = 1000000;
                        fileinfo.retries = 3;
                        startupload(true);
                    }
                },

                // Optional upload handler function to finalize an uploaded file on a server (e.g. move from a temporary directory to the final location).
                onfinishedupload: function(finalize, fileinfo) {
                    console.log('------------onfinishedupload');
                    console.log(fileinfo);

                    finalize(fileinfo);
                },

                // Optional upload handler function to receive permanent error notifications.
                onuploaderror: function(fileinfo, e) {
                    console.log('onuploaderror');
                    console.log(e);
                    console.log(fileinfo);
                },

                oninitdownload: function(startdownload, folder, ids, entries) {
                    console.log('oninitdownload');
                    console.log(ids);
                    console.log(entries);
                    // Simulate network delay.
                    setTimeout(function() {
                        // Set a URL and params to send with the request to the server.
                        var options = {};

                        // Optional:  HTTP method to use.
                        //				options.method = 'POST';

                        options.url = 'filemanager/';

                        options.params = {
                            action: 'download',
                            path: JSON.stringify(folder.GetPathIDs()),
                            ids: JSON.stringify(ids),
                            xsrftoken: 'asdfasdf'
                        };

                        // Optional:  Control the download via an in-page iframe (default) vs. form only (new tab).
                        //				options.iframe = false;

                        startdownload(options);
                    }, 250);
                },

                ondownloadstarted: function(options) {
                    console.log('started');
                    console.log(options);
                },

                ondownloaderror: function(options) {
                    console.log('error');
                    console.log(options);
                },

                // Calculated information must be fully synchronous (i.e. no AJAX calls).  Chromium only.
                ondownloadurl: function(result, folder, ids, entry) {
                    console.log('ondownloadurl');
                    console.log(folder);
                    console.log(ids);
                    console.log(entry);
                    result.name = (ids.length === 1 ? (entry.type === 'file' ? entry.name : entry.name + '.zip') : 'download-' + Date.now() + '.zip');
                    result.url = 'http://127.0.0.1/filemanager/?action=download&xsrfdata=asdfasdfasdf&xsrftoken=asdfasdf&path=' + encodeURIComponent(JSON.stringify(folder.GetPathIDs())) + '&ids=' + encodeURIComponent(JSON.stringify(ids));
                },

                oncopy: function(copied, srcpath, srcids, destfolder) {
                    console.log('oncopy');
                    console.log(srcpath);
                    console.log(srcids);
                    console.log(destfolder.GetPathIDs());
                    // Simulate network delay.
                    setTimeout(function() {
                        // Fill an array with copied destination folder entries from the server.
                        var entries = [];

                        copied(true, entries);
                    }, 250);
                },

                onmove: function(moved, srcpath, srcids, destfolder) {
                    console.log('onmove');
                    console.log(srcpath);
                    console.log(srcids);
                    console.log(destfolder.GetPathIDs());
                    // Simulate network delay.
                    setTimeout(function() {
                        // Fill an array with moved destination folder entries from the server.
                        var entries = [];

                        moved(true, entries);
                    }, 250);
                },

                ondelete: function(deleted, folder, ids, entries, recycle) {
                    console.log('ondelete');
                    console.log(folder);
                    console.log(ids);
                    console.log(entries);
                    console.log(recycle);
                    // Ask the user if they really want to delete/recycle the items.
                    if (!recycle && !confirm('Are you sure you want to permanently delete ' + (entries.length == 1 ? '"' + entries[0].name + '"' : entries.length + ' items') + '?')) deleted('Cancelled deletion');
                    else {
                        // Simulate network delay.
                        setTimeout(function() {
                            deleted(true);
                        }, 250);
                    }
                },
            });

            //fe.Focus();

            // Verify that there aren't any leaked globals.
            setTimeout(function() {
                // Create an iframe and put it in the <body>.
                var iframe = document.createElement('iframe');
                document.body.appendChild(iframe);

                // We'll use this to get a "pristine" window object.
                var pristineWindow = iframe.contentWindow.window;

                // Go through every property on `window` and filter it out if
                // the iframe's `window` also has it.
                console.log(Object.keys(window).filter(function(key) {
                    return !pristineWindow.hasOwnProperty(key)
                }));

                // Remove the iframe.
                document.body.removeChild(iframe)
            }, 15000);

            /*
            	// Test destroy.
            	setTimeout(function() {
            		fe.Destroy();
            	}, 20000);
            */
        })();
    </script>
</body>

</html>