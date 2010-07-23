#!/usr/bin/env python

'''Generate an index.html file for each test suite from its
test_suite description file. Then create a "master" index.html
file that installs Mahara and runs every test suite'''

import os

SHARED = 'shared'
INDEX = 'index.html'
PROCEDURE = 'test_suite'
FRONT_MATTER = '''<html>
    <head>
        <meta http-equiv="content-type" content="text/html; charset=ISO-8859-1">
        <title>Mahara Test Suite</title>
    </head>
    <body>
        <table cellpadding="1" cellspacing="1" border="1">
            <tbody>
                <tr><td><strong>Mahara Test Suite</strong></td></tr>
'''
BACK_MATTER = '''            </tbody>
        </table>
    </body>
</html>'''
START = '<tr><td><a href="'

ignore = ['.', './shared', './server', './basic-install']

def generate_index(root, files):
    '''Parse the test procedure file and generate a test
    suite file that can be understood by Selenium.'''
    content = ''
    f = open(os.path.join(root, PROCEDURE), 'r')
    tests = list(s.strip() for s in f.readlines())
    f.close()
    for test in tests:
        if test.find('#', 0, 1) == 0:
            continue;
        if test != '':
            if test + '.html' in files:
                path = '.'
            elif test + '.html' in os.listdir(SHARED):
                path = '../%s' % SHARED
                # FIXME: this assumes test suites are only 1 level deep
            else:
                print 'Nonexistant test [%s]' % test
                continue
            line = '%s<tr><td><a href="%s/%s.html">%s</a></td></tr>\n' % (' ' * 16, path, test, test)
            content += line
    f = open(os.path.join(root, INDEX), 'w')
    f.write('%s%s%s' % (FRONT_MATTER, content, BACK_MATTER))
    f.close()
    return content
    
if __name__ == '__main__':
    master_content = ''
    master_content += generate_index('./basic-install', os.listdir('./basic-install')).replace('../shared', 'shared').replace('./', '%s/' % 'basic-install')
    for root, dirs, files in os.walk('.', topdown=False):
        if PROCEDURE in files and root not in ignore:
            master_content += generate_index(root, files).replace('../shared', 'shared').replace('./', '%s/' % root)
        elif root not in ignore:
            print "Directory [%s] contains no test procedure file [%s]" % (root, PROCEDURE)
    f = open('TestSuite.html', 'w')
    f.write('%s%s%s' % (FRONT_MATTER, master_content, BACK_MATTER))
    f.close()

