WELCOME TO AMLVaran
===================

This is AMLVaran - a web-based platform for detecting and assessing genetic mutations
from targeted Next-Generation sequencing experiments.

The software was developed as a PhD project at the University of Muenster, Germany.

(C) 2015-2019 Christian Wünsch

This is open source software, licensed under GPL v3.


WARNING!
========

This software is intended for RESEARCH USE only!

The software components need to be adapted to local reqiurements.
Especially the variant calling parameters are to be adapted for the type of data to be used.

Importantly, the system needs to be assembled and validated locally before use!

This code is provided 'AS IS' and any express or implied warranties, including,
but not limited to, the implied warranties of merchantability and fitness for a particular purpose
are disclaimed.


Installation
============

Please use a recent Linux environment, e.g. Debian or Ubuntu.
Please make sure, that you have got at least 4 CPU cores, 8 GB of RAM and 500 GB free disk space.

1.) Install Docker and docker-compose and dependencies.

2.) If you want to use GATK 3 as a variant caller, please download it and place GenomeAnalysisTK.jar into the amlvaran root directory.

3.) The script Docker_start.sh guides you through the process of assembling and configuration of the software components.
    Additional ressources will be downloaded.


Running
=======

After installation is complete, 3 Docker containers (web interface, database and worker) should be running.

Open http://127.0.0.1 in your webbrowser to access the web interface.

You can first login with the demo account: user "demo", passwort "123456".
