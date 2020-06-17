## FileAttach

FileAttach is a tool to create file collections.
Package provides UI for MODx Manager to upload files to Resources,
manage file list, view download statistics, snippet for front-end listing.
Allows to count downloads, keep files privately (without direct url),
calculate SHA1 hash for uploaded files. Works with MediaSource.
Provides MediaSource to view tree with Resources and files attached to them.

## How to Export

First, clone this repository somewhere on your development machine:

`git clone http://github.com/13hakta/FileAttach.git ./`

Then, create the target directory where you want to create the file.

Then, navigate to the directory FileAttach is now in, and do this:

`git archive HEAD | (cd /path/where/I/want/my/new/repo/ && tar -xvf -)`

(Windows users can just do git archive HEAD and extract the tar file to wherever
they want.)

Then you can git init or whatever in that directory, and your files will be located
there!

## Configuration

Now, you'll want to change references to FileAttach in the files in your
new copied-from-FileAttach repo to whatever name of your new Extra will be. Once
you've done that, you can create some System Settings:

- 'mynamespace.core_path' - Point to /path/to/my/extra/core/components/extra/
- 'mynamespace.assets_url' - /path/to/my/extra/assets/components/extra/

Then clear the cache. This will tell the Extra to look for the files located
in these directories, allowing you to develop outside of the MODx webroot!

## Information

Note that if you git archive from this repository, you may not need all of its
functionality. This Extra contains files and the setup to do the following:

- Integrates a custom table of "FileItem"
- A snippet listing Items sorted by name and templated with a chunk
- A snippet showing link to get inline content
- A custom manager page to manage FileItem on

If you do not require all of this functionality, simply remove it and change the
appropriate code.

Also, you'll want to change all the references of 'FileAttach' to whatever the
name of your component is.

## Copyright Information

FileAttach is distributed as GPL (as MODx Revolution is), but the copyright owner
(Vitaly Chekryzhev) grants all users of FileAttach the ability to modify, distribute
and use FileAttach in MODx development as they see fit, as long as attribution
is given somewhere in the distributed source of all derivative works.