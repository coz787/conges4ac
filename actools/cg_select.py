#!/usr/bin/env python 
# -*- coding: utf-8 -*-  
'''conges_exmgr1.py: extract manager 1; 
generic script to extract data of conges database ;
option are listed in usage function '''

import sys, os, pprint, string, re, time, ldap # , getopt
from traceback import print_exc
from decimal import Decimal 
from datetime  import date
import MySQLdb
import MySQLdb.cursors

from cg_lib import CDoer 

scmd = ""   
version = "1_0"
def usage(cmd):
    fmt = ''' Usage: %s \
[--help] [--debug] --dbid=<filename> --cmd=commande [--chelp] [--optioni=sthing]

dbid file should define { 'host':, 'database':, 'user':, 'pw': }
cmd among [ %s ]  
'''
    print fmt   %  (cmd, string.join(lcmde," ")) 

def neatconflines(ofile): 
    lrealine = [] 
    while 1 :
        sali = ofile.readline()
        if sali == "" : break
        elif sali[0] == "#" : pass # discard comment 
        else:
            lrealine.append(sali[:-1])
    return string.join(lrealine)

def mygetopt(cmd,largs):
    ''' process argument and if success, return	
command name, debug mode , odbifile= database access, la commande , 
un dictionnaire avec les options non traitees '''
    lpathitem = string.split(sys.argv[0],'/')
    sacmd = lpathitem[-1]
    scom = "" 
    debug = 0
    odbifile =  None
    dallopt, dropt = {}, {}  
    idx = 0 
    soptegex1 = re.compile('^--([^\=]+)\=(.*)')
    soptegex2 = re.compile('^--(.*)')
    while idx < len(largs) :
        s1m = soptegex1.match(largs[idx])
        if s1m : 
            dallopt[s1m.group(1)] = s1m.group(2)
        else :
            s2m = soptegex2.match(largs[idx])
            if s2m : 
                dallopt[s2m.group(1)] = 1
            else :
                print "arg %s is not well formatted " % largs[idx]
                usage(sacmd)
                sys.exit(1)
        idx += 1 
    for (skey,svalue) in dallopt.items() :
        if skey == "help" :
            usage(sacmd)
            sys.exit(1)
        elif skey == "debug":
            debug = 1
        elif skey == "dbid" :
            try:
                odbifile = open(svalue)
            except:
                print "database  id file %s cannot be opened" % svalue
                usage(sacmd)
                sys.exit(1)
        elif skey  ==  "cmd":
            scom = svalue
            if scom not in lcmde :
                scom = "" 
            if scom == "" :
                print " a commande among %s should be given" % string.join(lcmde," ")
                usage(sacmd)
                sys.exit(1)
        else:
            dropt[skey] = svalue
    if odbifile == None :
        print "--dbid option is mandatory"
        usage(sacmd)
        sys.exit(1)
    if scom == "" :
        print "--cmd is mandatory ; value among (%s) should be given" % string.join(lcmde," ")
        usage(sacmd)
        sys.exit(1)
    if debug: 
        print "dallopt is ", dallopt
        print "dropt is ", dropt 
    return sacmd, debug, odbifile, scom, dropt

class LdapSearch:
    def __init__(self,lserver,lport,lmode,ldn,lpw):
        if lmode == 'ldaps' :  # required to avoid checking ssl certif in ldaps 
            ldap.set_option(ldap.OPT_X_TLS_REQUIRE_CERT, ldap.OPT_X_TLS_NEVER)
        try:
            self.con = ldap.initialize("%s://%s:%s" % (lmode,lserver,lport))
            self.nbind = self.con.bind(ldn, lpw)
            try:
                osident =  self.con.whoami_s() 
            except:
                osident = "anonymous" 
            # print "nbind is" , nbind 

        except ldap.INVALID_CREDENTIALS, e: 
            print "virtual dn/passwd is incorrect." , e 
            sys.exit(1)
        except ldap.SERVER_DOWN, e:
            print "server not reachable", e 
            sys.exit(1)
        except:
            print_exc()
            sys.exit(1)

    def search(self,sbasedn,oscope,sfilter,lattribs):
        lout = [] 
        osearch = self.con.search(sbasedn,oscope,sfilter,lattribs)
        while 1 :
            try:
                lresult =  self.con.result(osearch, 0, 5.0)   # all=0, timeout=5.0 sec
            except ldap.TIMEOUT, e :
                time.sleep(1)
                continue 
            except ldap.ADMINLIMIT_EXCEEDED, e :
                print "except=", e 
                time.sleep(1)
                continue # break
            except : 
                print_exc()
                break
#            print lresult
            if lresult[0] == ldap.RES_SEARCH_ENTRY or lresult[0] == ldap.RES_SEARCH_REFERENCE :
                lout.append(lresult[1][0])
            elif lresult[0] == ldap.RES_SEARCH_RESULT: 
                break 
        return lout 
    def search_s(self,sbasedn,oscope,sfilter,lattribs):
        # lout = [] 
        osearch = self.con.search_s(sbasedn,oscope,sfilter,lattribs)
        return osearch

oadoer = CDoer() 
lcmde = oadoer.get_ldometh() 

if __name__ == '__main__':
    #0 get parameter/arg 
    scmd, ndebug, odbif, scommand, dopt = mygetopt(sys.argv[0],sys.argv[1:])
    oadoer.set_debug(ndebug)
    #1.1 get access to database  
    try:
        ddcid = eval(neatconflines(odbif))
    except:
        print_exc()
        print "database id file cannot be evaluated"
        sys.exit(1)
    if ndebug: print ddcid
    #1.2 check acess to conges database  
    try:
# see charset='latin1' charset='utf8'  option 
        if ddcid.has_key('port'): 
            odbconn_std = MySQLdb.connect( 
                ddcid['host'],ddcid['user'],ddcid['pw'], ddcid['database'], 
                port=ddcid['port'], charset=ddcid['charset'], use_unicode=1)
            odbconn_dict = MySQLdb.connect( 
                ddcid['host'],ddcid['user'],ddcid['pw'], ddcid['database'], 
                port=ddcid['port'], charset=ddcid['charset'], use_unicode=1,  
                cursorclass=MySQLdb.cursors.DictCursor)
        else :
            odbconn_std = MySQLdb.connect( 
                ddcid['host'],ddcid['user'],ddcid['pw'], ddcid['database'], 
                charset=ddcid['charset'], use_unicode=1)
            odbconn_dict = MySQLdb.connect( 
                ddcid['host'],ddcid['user'],ddcid['pw'], ddcid['database'], 
                charset=ddcid['charset'], use_unicode=1,  cursorclass=MySQLdb.cursors.DictCursor)

        odbcursor_std = odbconn_std.cursor() 
        odbcursor_dict = odbconn_dict.cursor() 
    except:
        print_exc()
        print "acces to %s/%s for user refused" % \
            (ddcid['host'], ddcid['database'], ddcid['user'])
        sys.exit(1)
    #1.3 build ldap accessor if defined and pass it as ddcid['ldap']['oserv'] 
    if ddcid.has_key('ldap'):
        try:
            olsearch = LdapSearch(ddcid['ldap']['server'],ddcid['ldap']['port'],
                                  ddcid['ldap']['mode'],ddcid['ldap']['dn'],ddcid['ldap']['pw'])
        except:
            print "erreur acces / parametre serveu ldap" 
            print_exc()
            sys.exit(1)
        ddcid['ldap']['oserv'] = olsearch

    # 2. launching commande with option 
    # do_print_user_solde(dopt, odbcursor, ddcid, 'didier.pavet' )
    # some meta-programming with caution 
    srealcmd = "do_%s" % scommand
    try:
        # meth is the named method "do_sthing" from object oadoer 
        meth = getattr(oadoer, srealcmd)
    except:
        print_exc()
        sys.exit(1)
    # print string.join(dopt.keys()," ")     print dopt 
    if "chelp" in dopt.keys():
        # usage(scmd)
        print meth.__doc__
        sys.exit(1)
    else :
        try:
            nret = meth(dopt, odbcursor_std, odbcursor_dict, ddcid) 
        except:
            print_exc()
            sys.exit(1)
    if nret < 0:
        usage(scmd)
        sys.exit(1)
    sys.exit(0) 
