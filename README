___  ___      _ _   _ _____                          
|  \/  |     | | | (_)  __ \                         
| .  . |_   _| | |_ _| |  \/ ___  _   _ _ __ ___ ___ 
| |\/| | | | | | __| | | __ / _ \| | | | '__/ __/ _ \
| |  | | |_| | | |_| | |_\ \ (_) | |_| | | | (_|  __/
\_|  |_/\__,_|_|\__|_|\____/\___/ \__,_|_|  \___\___|

=====================================================

Author: Christopher Clark (@Frencil)
March 24, 2012

Gource is an open source library for visualizing the growth of
a version controlled source code repository over time with
dynamic, colorful animations.

On GitHub:   https://github.com/acaudwell/Gource

MultiGource is a little script I developed to recurse through
subdirectories containing multiple Git repositories and condense
their logs into a single custom-format log that can be fed to
Gource to produce one massive visualization of many repos.


GENERATING THE LOG
==================

1. Clone as many repositories as you like into a top-level
   directory. They can be buried in subdirectories.
   Only Git repositories are supported at this time.

2. Edit log_generator.php to define your root path and colors
   (see comments in USER DEFINED VALUES section)

3. From the location of log_generator.php:
   `./php log_generator.php > {LOGFILE}`
   Where {LOGFILE} is the destination of your custom-format log.


GENERATING THE VISUALIZATION
============================

Here's the basic command to get your visualization running at 720p:

% gource --load-config /path/to/multigource.conf -1280x720 {LOGFILE}

WARNING! Running Gource on many big projects like this can take a
long time! Watching your visualization as it renders may be
excrutiatingly slow (and will vary in speed as the complexity of
the content varies).

RECOMMENDATION: Render your Gource visualization as a stream and pipe
it to ffmpeg to get a video file that runs at a consistent speed, can
be edited or uploaded to the internet, whatever.

To do this you'll need to install ffmpeg. Just get it, it's awesome.

Here's an updated command that turns Gource into a stream and pipes it
to ffmpeg. The extra flags on the ffmpeg part are tuned to produce a 720p
video file that has a good balance of high quality and decent file size.

% gource --load-config  /path/to/multigource.conf -1280x720 {LOGFILE} --output-ppm-stream - | \
  ffmpeg -an -threads 4 -y -vb 4000000 -s 1280x720 -r 30 -f image2pipe -vcodec ppm -i - {OUTPUTFILE}

Please refer to ffmpeg documentation to understand these flags and how
to tweak them. {OUTPUTFILE} is the path to the final video and its format
will be automatically determined by the extension you choose.
(e.g. file.mov, file.flv, file.mp4)
