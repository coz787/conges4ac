# -*- coding: utf-8 -*-  
'''cg_lib.py: real set of class, function used by cg_select'''

import datetime, ldap, sys, os, pprint, string, re, time # , getopt
from traceback import print_exc
from decimal import Decimal 
from datetime  import date
import MySQLdb


def neat_email(semail):
    '''nettoie adresse email pas propre'''
    # return semail 
    if semail == None :
        return ""     
    s = string.replace(semail, '<','')
    s = string.replace(s, '>','')
    return s 
def msd2latin1_old(ustr):
    ''' mysql data to latin1 ''' 
    print ustr, type(ustr), ustr.__class__, id(ustr)
    if ustr == None :
        return "" 
    else :
        try: 
            sret = str(ustr.encode('latin-1', 'ignore'))
        except AttributeError:
            sret = "%d" % ustr
        return sret
def msd2string(omsdata):
    ''' mysql data to latin1 ''' 
    # print type(omsdata), omsdata.__class__, id(omsdata)
    if omsdata == None :
        sret =  "" 
    elif isinstance(omsdata, Decimal)  :
        sret = "%-.2f" % omsdata
    elif isinstance(omsdata, long)  :
        sret = "%d" % omsdata
    elif isinstance(omsdata, int)  :
        sret = "%d" % omsdata
    elif isinstance(omsdata, date)  :
        # sret = "date" 
        sret = omsdata.strftime('"%Y:%m:%d"') # :%X")
    elif isinstance(omsdata, unicode) :
        sret = '"%s"' % str(omsdata.encode('latin-1', 'ignore'))
    elif isinstance(omsdata, str):
        sret = '"%s"' %  omsdata 
    else:
        sret = "__unkowntype__%s" % type(omsdata) 
    # print sret 
    return sret 
def s2_latin1(ustr) :
    if ustr == None :
        return "" 
    else :
        return str(ustr.encode('latin-1', 'ignore'))
def e_utf8(ustring):
    #return ustr.decode('utf-8','ignore').encode('latin-1', 'ignore')
    # return ustring.encode('utf-8')
    # return unicode(ustring , 'latin-1' )
    #return ustring.decode('utf-8').encode('latin-1' )
    # return ustring.encode('latin-1' )
    return str(ustring) 

sexec_sql_file_doc = ''' php equivalent 
function execute_sql_file($file, $mysql_link, $DEBUG=FALSE)
{
	// lecture du fichier SQL
	// et execution de chaque ligne ....
	$lines = file ($file);
	$sql_requete="";
	foreach ($lines as $line_num => $line)
	{
		$line=trim($line);
	    if( (substr($line, 0, 1)!="#") && ($line!="") )  //on ne prend pas les lignes de commentaire
	    {
			$sql_requete = $sql_requete.$line ;
			if(substr($sql_requete, -1, 1)==";") // alors la requete est finie !
			{
				if($DEBUG==TRUE)
					echo "$sql_requete<br>\n";
				$result = requete_mysql($sql_requete, $mysql_link, "execute_sql_file", $DEBUG);
				$sql_requete="";
			}
	    }
	}
	return TRUE;

}
'''

def exec_sql_file(cursor,sql_file,runmode="dryrun"):
    print "\n[INFO] Executing SQL script file: '%s'" % (sql_file)
    statement = ""

    for line in open(sql_file):
        if re.match(r'^#', line):  # ignore sql comment lines
            continue
        if not re.search(r'[^-;]+;', line):  # keep appending lines that don't end in ';'
            statement = statement + line
        else:  
# when you get a line ending in ';' then exec statement and reset for next statement
            statement = statement + line
#print "\n\n[DEBUG] Executing SQL statement:\n%s" % (statement)
            if runmode == "realrun" : 
                try:
                    cursor.execute(statement)
                # except (OperationalError, ProgrammingError) as e:
                except: 
                    print "error on sql statement %s" % statement 
                    print_exc()
                    sys.exit(1)
                    # print "\n[WARN] MySQLError during execute statement \n\tArgs: '%s'" % (str(e.args))
                statement = ""
            else:
                print "would do %s" % statement 

ndebug=0 
class CDoer:
    def __init__(self):
        # print "doer dict", self.__class__.__dict__
        # establish a list of do_ method 
        self.ldometh = [] 
        for sme in dir(self):
            if sme[0:3] == "do_" :
                 self.ldometh.append(sme[3:])
        self.ltechuser = ['admin', 'conges']
    def set_debug(self,ndbg):
        ndebug = ndbg 

    def get_ldometh(self):
        return self.ldometh
    def do_echo(self,*unarg, **narg) :
        print 
        print "do_echo , unamed arg" 
        for saarg in unarg:
            print  saarg,
        print 
        print "do_echo , named arg" 
        for saarg in narg:
            print  saarg, 
    def do_print_table(self, dopt, odbcursor, odbcursordict, ddcid):
        ''' option --table required ; --where for where condition'''
        if dopt.has_key('table'):
            stablename = dopt['table']
        else : 
            print "a valid table should be given using --table" 
            return -1 
        if dopt.has_key('where'):
            swhere = dopt['where'] 
        else: 
            swhere = ""
        ssqlsel = "select * from %s" % stablename 
        if swhere != "" : ssqlsel += " where %s" % swhere 
        ssqlsel += " ;" 
        # print "ssqlsel = ", ssqlsel
        try:
            odbcursor.execute(ssqlsel)
        except:
            print_exc()
            print "print_table from database %s on table %s raises an error" % \
                ( ddcid['database'], stablename )
            return -1 
        while 1 :
            row = odbcursor.fetchone()
            if row == None :
                break
            print string.join(map(msd2string, row) ,";") + ";"  

        return 1 

    def do_show_desc(self, dopt, odbcursor, odbcursordict, ddcid):
        '''new version discovering automatically the list of table belonging to 
the named database. no options. ''' 
        ssqllistable = "SELECT table_name FROM information_schema.tables WHERE table_type = 'base table' AND table_schema='%s' order by table_name " ;
        ssqldesc = "SHOW COLUMNS FROM %s.%s ; "

        sqlorder = ssqllistable % ddcid['database'] 
        ltablelist = [] 
        try:
            odbcursor.execute(sqlorder)
        except:
            print_exc()
            print "%s raises an error  " % sqlorder
            return -1 
        while 1 :
            row = odbcursor.fetchone()
            if row == None :
                break
            ltablelist.append(s2_latin1(row[0])) 
        # print pprint.pformat(ltablelist)
        for atable in ltablelist: 
            # print "doing %s" % atable 
            sqlorder = ssqldesc % (ddcid['database'],atable)
            try:
                odbcursor.execute(sqlorder)
            except:
                print_exc()
                print "%s raises an error  " % sqlorder
                return -1 
            while 1 :
                row = odbcursor.fetchone()
                if row == None :
                    break
                print "%s;%s" % (atable, string.join(map(s2_latin1, row) ,";")) 
        return 1 

    def nodo_show_desc_v1(self, dopt, odbcursor, odbcursordict, ddcid):
        # ssqldesc = "SHOW COLUMNS FROM %s" % stablename 
        # ssqldesc = "SHOW COLUMNS FROM conges_users ;" 
        # ssqldesc = "SHOW DATABASES;" 
        # ssqldesc = "SHOW COLUMNS FROM %s.conges_users " % ddcid['database']
        ssqldesc = "SHOW COLUMNS FROM %s.%s ; "
#         ssqldesc = "desc;" 
        lknowntable = [
'conges_artt',
'conges_config',
'conges_echange_rtt',
'conges_edition_papier',
'conges_groupe',
'conges_groupe_grd_resp',
'conges_groupe_resp',
'conges_groupe_users',
'conges_jours_feries',
'conges_jours_fermeture',
'conges_logs',
'conges_mail',
'conges_periode',
'conges_solde_user',
'conges_solde_edition',
'conges_type_absence',
'conges_users',
]
        for atable in lknowntable:
            # print "doing %s" % atable 
            sqlorder = ssqldesc % (ddcid['database'],atable)
            try:
                odbcursor.execute(sqlorder)
            except:
                print_exc()
                print "%s raises an error  " % sqlorder
                return -1 
            while 1 :
                row = odbcursor.fetchone()
                if row == None :
                    break
                print "%s;%s" % (atable, string.join(map(s2_latin1, row) ,";")) 

        return 1 
        
    def do_show_table(self, dopt, odbcursor, odbcursordict, ddcid):
        ''' option --table required ; option --name just to obtain field name '''
        if dopt.has_key("name") :
            nfield =  1 
        else :
            nfield =  0
        if dopt.has_key('table'):
            stablename = dopt['table']
        else : 
            print "a valid table should be given using --table" 
            return -1 
        # _tbc 
        ssqlshow = "SHOW COLUMNS FROM %s" % stablename 
        try:
            odbcursor.execute(ssqlshow)
        except:
            print_exc()
            print "show_table from database %s on table %s raises an error" % \
                ( ddcid['database'], stablename )
            return -1 
        while 1 :
            row = odbcursor.fetchone()
            if row == None :
                break
            print string.join(map(s2_latin1, row) ,";") + ";" 

        return 1 
    def olddo_print_all_users(self, dopt, odbcursor, odbcursordict, ddcid):
        ''' no option required ; option --valid to exclude technical login '''
        if dopt.has_key("valid"):
            nvalid =  1 
        else :
            nvalid = 0 
        try:
            odbcursor.execute("select u_login, u_nom, u_prenom, u_email, u_is_resp, u_is_admin, u_see_all, u_is_gest, u_resp_login from `conges_users` order by u_nom, u_prenom ") 
            nuser = 0 
            while 1 :
                row = odbcursor.fetchone()
                if row == None :
                    break
                # print row 
                if nvalid :
                    if row[0] in self.ltechuser :
                        # user excluded 
                        continue 
                # else 
                #print "%s;%s;%s;%s;" % ( row[0], s2_latin1(row[1]), s2_latin1(row[2]),  
                #                s2_latin1(neat_email(row[3])) )
                lrow = [] 
                for e in row[0:3] : 
                    lrow.append(e)
                lrow.append(neat_email(row[3]))
                lrow.append(row[8])
                suserstat = "" 
                if row[5] == 'Y' :
                    suserstat += 'admin-' 
                if row[4] == 'Y' :
                    suserstat += 'resp-' 
                if row[6] == 'Y' :
                    suserstat += 'seeall-' 
                if row[7] == 'Y' :
                    suserstat += 'gest-' 
                lrow.append(suserstat)
                print string.join(map(msd2string, lrow) ,";") + ';'

                # print map(s2_latin1, row)        # map(type, row) # map(e_utf8, row)
                nuser += 1 
                # ltablen.append(row[0])
        except:
            print_exc()
            print "%s.conges_users table not reachable" % ddcid['database']
        print 
        print "%d utilisateurs references dans la base %s.conges_users" % \
            (nuser, ddcid['database'])
        return 1 # print "%s ok." % scmd 

    def do_print_all_users(self, dopt, odbcursor, odbcursordict, ddcid):
        ''' no option required ; option --valid to exclude technical login 
this version use odbcursordict and is flexible enough to cope with old version of php_conges '''
        # print odbcursor, odbcursordict
        if dopt.has_key("valid"):
            nvalid =  1 
        else :
            nvalid = 0 
        lselect = [ "select u_login, u_nom, u_prenom, u_email, u_is_resp, u_is_admin, u_see_all, u_is_gest, u_resp_login from `conges_users` order by u_nom, u_prenom ",
                    "select u_login, u_nom, u_prenom, u_email, u_is_resp, u_is_admin, u_resp_login from `conges_users` order by u_nom, u_prenom " ]
        bsucces = False 
        for aselect in lselect :
            try:
                odbcursordict.execute(aselect)
            except :
                print_exc()
                pass 
            else:
                bsucces = True 
                break 
        if bsucces :
            nuser = 0 
            while 1 :
                dselect = odbcursordict.fetchone()
                if dselect == None :
                    break 
                # <check_unicode_output>
                # print string.join(map(msd2string, dselect.values()) ,";") + ';'
                # continue 
                # </check_unicode_output>
                if nvalid :
                    if dselect["u_login"] in self.ltechuser :
                        # user excluded 
                        continue 
                #if dselect["u_resp_login"] != "christian.cilia" :
                #    continue 
                if not dselect.has_key("u_see_all"):
                    dselect["u_see_all"] = ""
                if not dselect.has_key("u_is_gest"):
                    dselect["u_is_gest"] = ""
                    
                lrow = [] 
                lrow.append(len(dselect["u_login"]))

                lrow.append(dselect["u_login"])
                lrow.append(dselect["u_nom"])
                lrow.append(dselect["u_prenom"])
                lrow.append(neat_email(dselect["u_email"]))
                lrow.append(dselect["u_resp_login"])

                suserstat = "" 
                if dselect["u_is_admin"] == 'Y' :
                    suserstat += 'admin-' 
                if dselect["u_is_resp"] == 'Y' :
                    suserstat += 'resp-' 
                if dselect["u_see_all"] == 'Y' :
                    suserstat += 'seeall-' 
                if dselect["u_is_gest"] == 'Y' :
                    suserstat += 'gest-' 
                lrow.append(suserstat)
                print string.join(map(msd2string, lrow) ,";") + ';'

                # print map(s2_latin1, row)        # map(type, row) # map(e_utf8, row)
                nuser += 1 
            #end while 
            print "%d utilisateurs references dans la base %s.conges_users" % \
                (nuser, ddcid['database'])
     
        else:
            print "%s.conges_users table not requestable" % ddcid['database']
        return 1 
    def do_print_dis_periode(self, dopt, odbcursor, odbcursordict, ddcid):
        sdispersql = "select distinct p_etat from conges_periode"
        try:
            odbcursor.execute(sdispersql)
            while 1 :
                row = odbcursor.fetchone()
                if row == None :
                    break
                print row[0] 
        except:
            print_exc()
            print "method do_print_dis_periode on except" 
            return -1 
        return 1 
        
    def do_check_users_ldap(self, dopt, odbcursor, odbcursordict, ddcid):
        ''' no option required '''
        luserdefect = [] # will be compose of ( row, + "explanation") 
        try:
            oldapsearcher = ddcid['ldap']['oserv']
        except :
            print "**error: check_users_ldap requires a valid and accessible ldap server to work properly."
            return 1 
        try:
            odbcursor.execute("select u_login, u_nom, u_prenom, u_email from `conges_users` order by u_login ") 
        except:
            print_exc()
            print "%s.conges_users table not reachable" % ddcid['database']
            return 1 
        lsearchmail = ['mail','mailEquivalentAddress'] 

        while 1 :
            row = odbcursor.fetchone()
            if row == None :
                break
            bstatus = True  
            sexplain = ""
            sauid = row[0]
            # hypothese annuaire est insensible a la casse 
            if row[3]: 
                samail = string.lower(row[3]) 
            else:
                samail = "" 
            ldirmail = [] 
            if sauid in self.ltechuser: # admin, conge are excluded 
                continue 
            lres = oldapsearcher.search(
                ddcid['ldap']['domain'], ldap.SCOPE_SUBTREE,
                "(uid=%s)" % sauid , lsearchmail )
            if len(lres) == 1 : 
                lfound = lres[0]
                # on place dans ldirmail l'ensemble des adresse definis 
                # par les 2 attributs en minuscules 
                for anattr in lsearchmail :
                    if lfound[1].has_key(anattr):
                        for anemail in lfound[1][anattr] :
                            ldirmail.append(string.lower(anemail)) 
                if samail not in ldirmail :
                    bstatus = False 
                    sexplain = "mail differs: app:%s directory:%s " % (samail,string.join(ldirmail,",")) 
            else :
                bstatus = False 
                sexplain = "account is missing in directory" 
            if not bstatus:
                myrow = list(row) 
                myrow.append(sexplain)
                luserdefect.append(myrow)
        # end while 
        if len(luserdefect) > 0 :
            print "#some accounts are deffective: "
            for arow in luserdefect:
                print string.join(map(msd2string,arow),';')
        else:
            print "#all accounts have been checked. "
        return 1

    def do_print_user_solde(self, dopt, odbcursor, odbcursordict, ddcid):
        '''--login required'''
        if dopt.has_key('login'):
            ulogin = dopt['login']
        else : 
            print "a login option should be given using --login" 
            return -1 
        try:
            odbcursor.execute("select u_login, u_nom, u_prenom from `conges_users` \
    where u_login = '%s'" % ulogin ) 
            print "{0:-^20} {1:-^12} {2:-^12}".format( "u_login", "u_nom", "u_prenom")  
            while 1 :
                row = odbcursor.fetchone()
                if row == None :
                    break
    # _tips notation *transform le tuple en liste d'argument pour fonction format  
                print "{0:<20} {1:<12} {2:<12}".format(*map(s2_latin1, row))
        except:
            print_exc()
            print "%s.conges_users table not reachable" % ddcid['database']
        try:
            sfmtheader="{0:-^6} {1:-^26} {2:-^10} {3:-^12} {4:-^12}"
            sfmtval1="{0: >6d} {1: <26} {2: <10} {3: >12.1f} {4: >12.1f}"
            srequest = "select conges_type_absence.ta_id, conges_type_absence.ta_libelle, conges_type_absence.ta_type, conges_solde_user.su_solde, conges_solde_user.su_nb_an from conges_type_absence, conges_solde_user where conges_solde_user.su_login = '%s' and  conges_solde_user.su_abs_id = conges_type_absence.ta_id order by conges_type_absence.ta_id "  %  ulogin
            odbcursor.execute( srequest) 
            print 
            print sfmtheader.format("taid", "nomconge", "type", "solde", "nb_par_an")
            while 1 :
                row = odbcursor.fetchone()
                if row == None :
                    break
                if ndebug: print row 
                print sfmtval1.format(row[0], s2_latin1(row[1]), s2_latin1(row[2]), row[3] ,row[4] )
        except:
            print_exc()   
            print "pb with %s request on database %s" % (srequest, ddcid['database'])
        return 1 

    def do_print_user_usage(self, dopt, odbcursor, odbcursordict, ddcid):
        '''--login required
optional --periode provide list of relevant conges_periode
         --year=2012,2013  not yet implemented '''
        if dopt.has_key('login'):
            slogin = dopt['login']
        else : 
            print "a login option should be given using --login" 
            return -1
        if dopt.has_key('year'):
            syear = dopt['year']
        else:
            lyear = None
        bperiode = dopt.has_key('periode') # boolean  _tbc 
        dconge_ta =  {} 
        sre1date = re.compile('(\D+):\D+:\D+')
        ssql2 = "select * from conges_type_absence" ; 
        try:
            odbcursor.execute(ssql2)
            while 1 :
                row = odbcursor.fetchone()
                if row == None : 
                    break
                dconge_ta[int(row[0])] = map(msd2string, row) 
        except:
            print_exc()
            print "do_print_user_usage on except conges_type_absence " 
            return -1
        # print "list of conges_type is %s " % string.join(lconge_type,";")
        lcta = dconge_ta.keys()
        lcta.sort()
        spsql1 = "select p_date_traitement, p_date_deb,p_demi_jour_deb,p_date_fin,p_demi_jour_fin,p_nb_jours, p_type, p_etat, p_login, p_commentaire from conges_periode where p_login='%s' and p_etat='ok' order by p_date_traitement " 

        dsolde_yt = {} # dict index by year and type 
        ssql1 = spsql1 % slogin 
        try:
            odbcursor.execute(ssql1)
            nnum = 0 
            while 1 :
                row = odbcursor.fetchone()
                if row == None : 
                    break
                # print row 
                try:
                    n_year = int(row[0].strftime('%Y'))
                except:
                    print "do_print_user_usage: error wrong year in conges_periode record a"
                try:
                    n_duration = row[5]
                except:
                    print "do_print_user_usage: error wrong year in conges_periode record c"
                try:
                    n_type = int(row[6])
                except:
                    print "do_print_user_usage: error wrong year in conges_periode record d"
                try:
                    dsolde_yt[n_year][n_type] +=  n_duration
                except:
                    if not dsolde_yt.has_key(n_year):
                        dsolde_yt[n_year] = {} 
                    dsolde_yt[n_year][n_type] =  n_duration
                nnum += 1
        except:
            print_exc()
            print "do_print_user_usage on except " 
            return -1 
        lyears = dsolde_yt.keys()
        lyears.sort()
        # print conges_type_absence en commentaire 
        print "# conges_type_absence" 
        for kcta in lcta :
            sex = "# " + string.join(dconge_ta[kcta],"-") + ";" 
            sex = string.replace(sex, '"','')
            sys.stdout.write("%s\n" % sex) 
        print "annee \ type;",
        for nt in lcta:
            sys.stdout.write("%d;" % nt)
        sys.stdout.write("\n")
        for nyear in lyears:
            print "%d;" % nyear, 
            for nt in lcta:
                if dsolde_yt[nyear].has_key(nt):
                    s3 = "%-2.2f;" % dsolde_yt[nyear][nt] 
                    sys.stdout.write(string.replace(s3,'.',','))       # to be imported swiftly in excel
                else:
                    sys.stdout.write(";")
            sys.stdout.write("\n")
        # print pprint.pformat(dsolde_yt)
        return 1 

    def do_print_afile_auser(self, dopt, odbcursor, odbcursordict, ddcid):
        '''--loginfile required : meaning a file made of 
one user login per line '''
        llogin = [] 
        if dopt.has_key('loginfile'):
            sloginf = dopt['loginfile']
            try:
                ologinf = open(sloginf)
                while 1:
                    saline = ologinf.readline()
                    if saline == "" :
                        break
                    llogin.append(saline[:-1])  # escaping linefeed 
            except:
                print_exc()
                print "file %s cannot be evaluated " % sloginf
                return -1 
        else : 
            print "a file name should be given using --loginfile ; this file should be made of one user login per line"
            return -1 
        if ndebug: print llogin
        for salogin in llogin:
            safile = "cf_%s.txt" % salogin
            try: 
                oafile = open(safile, 'w')
            except:
                print "cf file %s cannot be written " % safile 
            self.print_afile_auser(salogin, oafile, odbcursor, odbcursordict, ddcid)
            oafile.close()
        return 1 


    def print_afile_auser(self, salogin, oafile, odbcursor, odbcursordict, ddcid):
        '''write a conges user file on a file out - oafile which can stdout'''  
        oafile.write("dossier conge pour %s\n" % salogin)
        losqlrequest = [ { 'slegend' : "vue user", 
   'sqlorder' : "select u_login, u_nom, u_prenom from `conges_users` where u_login = '%s'" },
                        {'slegend' : "vue type_absence, solde_user ", 
   'sqlorder' : "select conges_type_absence.ta_short_libelle, conges_solde_user.su_solde, conges_solde_user.su_nb_an from conges_type_absence, conges_solde_user where conges_solde_user.su_login = '%s' and  conges_solde_user.su_abs_id = conges_type_absence.ta_id and conges_type_absence.ta_short_libelle in ('ca','rtt') order by conges_type_absence.ta_short_libelle " }, 
                        {'slegend' : "vue type_absence, periode ", 
   'sqlorder' : "select conges_type_absence.ta_short_libelle, conges_periode.p_login, conges_periode.p_date_deb, conges_periode.p_demi_jour_deb, conges_periode.p_date_fin, conges_periode.p_demi_jour_fin, conges_periode.p_nb_jours, conges_periode.p_commentaire, conges_periode.p_etat, conges_periode.p_edition_id, conges_periode.p_motif_refus, conges_periode.p_date_demande, conges_periode.p_date_traitement from conges_type_absence, conges_periode where conges_periode.p_login = '%s' and  conges_periode.p_type = conges_type_absence.ta_id order by conges_periode.p_date_deb, conges_periode.p_num " },
# order by conges_periode.p_date_deb
                        {'slegend' : "vue user groupe ", 
   'sqlorder' : "select conges_groupe.g_groupename, conges_groupe.g_comment, conges_groupe.g_double_valid  from conges_groupe, conges_groupe_users where conges_groupe.g_gid = conges_groupe_users.gu_gid and conges_groupe_users.gu_login ='%s'  order by conges_groupe.g_groupename " },

                        {'slegend' : "vue type_absence, solde_user ", 
   'sqlorder' : "" },
                        {'slegend' : "vue type_absence, solde_user ", 
   'sqlorder' : "" },
                        {'slegend' : "vue type_absence, solde_user ", 
   'sqlorder' : "" },

                        ] ; 

        for osqlr in losqlrequest[0:4]:
            oafile.write("---- %s ----\n" % osqlr['slegend'])
            try:
                odbcursor.execute(osqlr['sqlorder'] % salogin) 
            except:
                 print_exc()
            while 1 :
                row = odbcursor.fetchone()
                if row == None :
                    break
                oafile.write(string.join(map(msd2string, row) ,";") + ';\n')
    def do_print_userrights(self, dopt, odbcursor, odbcursordict, ddcid):
        ''' print users rights on conges database''' 
        print "_tbc"
        return 1 
    def do_print_last_c_periode(self, dopt, odbcursor, odbcursordict, ddcid):
        ''' print last number valid conges_periodes for conges database ; valid option
--number opt
--all means even pending conges_periodes 
--login mean only for identifed user 
--typeabs mean only these type of abs ''' 
        if dopt.has_key("all") : 
            nall =  1 
        else : 
            nall = 0 
        if dopt.has_key("login") :
            slogin =  dopt["login"] 
        else :
            slogin = "" 
        try:
            if dopt.has_key("typeabs") :
                ntypeabs = int(dopt["typeabs"]) 
            else:
                ntypeabs = 0 
        except:
            print_exc()
            print "option typeabs is not a number" 
            return -1
        try:
            if dopt.has_key("number") :
                nlast = int(dopt["number"]) 
            else:
                nlast = 20 
        except:
            print_exc()
            print "option number is invalid" 
            return -1
        if ndebug: print "nlast %d" % nlast 
        ssql1 = "select p_date_traitement, p_date_deb,p_demi_jour_deb,p_date_fin,p_demi_jour_fin,p_nb_jours, p_type, p_etat, p_login, p_commentaire, p_num from conges_periode" 
        if not nall or slogin != "" or ntypeabs != 0 :
            ssql1 += " where" 
            lsqlcond = [] 
            if slogin != "" :
                lsqlcond.append("p_login='%s'" % slogin)
            if not nall :
                lsqlcond.append("p_etat='ok'")
            if ntypeabs != 0 :
                lsqlcond.append("p_type=%d" % ntypeabs)
            if len(lsqlcond) >= 1 :
                for scond in lsqlcond[:-1] :
                    ssql1 += " %s " % scond + " and"
                ssql1 += " %s " %   lsqlcond[len(lsqlcond)-1]
        ssql1 += " order by p_date_traitement desc"
        if ndebug : print ssql1 
        try:
            odbcursor.execute(ssql1)
            nnum = 0 
            while 1 :
                row = odbcursor.fetchone()
                if row == None or (not nall and nnum > nlast) :
                    break
                print string.join(map(msd2string, row),";") + ";" 
                nnum += 1
        except:
            print_exc()
            print "do_print_last_c_periode on except " 
            return -1 
        return 1 
    def do_print_last_c_logs(self, dopt, odbcursor, odbcursordict, ddcid):
        ''' print backward last number record of conges_logs for conges database  ;
--number number of log entry (default 20) 
--login means event performed by user identified XOR 
--for means event performed for user identified 
'''
        if dopt.has_key("login") : 
            spar = dopt["login"] 
        else:
            spar = "" 
        if dopt.has_key("for") : 
            sfor = dopt["for"] 
        else:
            sfor = "" 

        try:
            if dopt.has_key("number") :
                nlast = int(dopt["number"]) 
            else:
                nlast = 20 
        except:
            print_exc()
            print "option number is invalid" 
            return -1
        ssql1 = "select log_id, log_p_num, log_user_login_par, log_user_login_pour, log_etat, log_comment, log_date from conges_logs "
        if spar != ""  or sfor != "" :
            ssql1 += " where " 
            if spar != "" :
                ssql1 += " log_user_login_par = '%s'" % spar 
            elif sfor != "" :
                ssql1 += " log_user_login_pour = '%s'" % sfor 

        ssql1 += " order by log_id desc"
        try:
            odbcursor.execute(ssql1)
            nnum = 0 
            while 1 :
                row = odbcursor.fetchone()
                if row == None or nnum > nlast :
                    break
                lrow = []
                for e in row: 
                    lrow.append(e) 
                lrow[6] =  lrow[6].strftime("%Y:%m:%d:%X")
                print string.join(map(msd2string, lrow),";") + ";" 
                nnum += 1
        except:
            print_exc()
            print "do_print_last_c_logs on except " 
            return -1 
        return 1
    def do_sql_usce(self, dopt, odbcursor, odbcursordict, ddcid):
        ''' sql to update conges enfant '''
        cid = 10 
        try:
            # odbcursor.execute("desc conges_solde_user")
            odbcursor.execute("select * from conges_solde_user where su_abs_id = %d " % cid)
            while 1 :
                row = odbcursor.fetchone()
                if row == None :
                    break
                # ldata = map(msd2string, row)
                ldata = row 
                # print map(type,ldata)
                # print ldata
                if ldata[0] not in self.ltechuser:
                    sphrase = "update conges_solde_user set su_nb_an = %-2.2f , su_solde = %-2.2f where \
su_login ='%s' and su_abs_id=%d ;" 
                    n_nban = ldata[2]
                    n_solde = ldata[3]
                    if n_nban != 0.0 or n_solde != 0.0 :
                        try:    
                            print sphrase % (n_nban,n_solde,ldata[0],cid) 
                        except:
                            print "wrong internal data"
                            print_exc()
                            return -1
        except:
            print_exc()
            print "err: do_sql_usce" 
            return -1
        return 1 

    def do_detect_w_artt(self, dopt, odbcursor, odbcursordict, ddcid):
        '''detecte les plages de prévisions artt courant manquante '''
        ssql_users = "SELECT u_login FROM conges_users ;" 
        ssql_detectp = "SELECT a_date_debut_grille,a_date_fin_grille from conges_artt WHERE a_login='%s' ORDER BY a_date_debut_grille ASC ;" 
        sdfgrillet = datetime.date(9999, 12, 31)
        lusers = [] 
        try:
            odbcursor.execute(ssql_users)
            while 1 :
                row = odbcursor.fetchone()
                if row == None :
                    break
                lusers.append(row[0]) 
        except:
            print_exc()
            print "method do_detect_w_artt " 
            return -1 
        # print "do_detect_w_artt" + pprint.pformat(lusers) 
        nind = 0 
        for auser in lusers :
            if auser in self.ltechuser :
                continue 
            #if nind > 5 :  # pour test 
            #    break 
            # pour un user valid 
            lartt = [] 
            try:
                ssql_detect = ssql_detectp % auser
                odbcursor.execute(ssql_detect)
                b_has_sdfgrillet = False 
                while 1 :
                    row = odbcursor.fetchone()
                    if row == None :
                        break
                    if row[1] == sdfgrillet :
                        b_has_sdfgrillet = True 
                    lartt.append(row) 
                nartt = len(lartt)
                try:
                    sdfg = lartt[nartt-1][1] 
                except:
                    print "do_detect_w_artt no artt define for %s ;" % auser 
                # if sdfg == sdfgrillet : 
                if  b_has_sdfgrillet : 
                    pass # ok
                else:
                    # print "do_detect_w_artt last artt for %s differes from standard [%s];" % (auser,sdfg)
                    print "do_detect_w_artt no item with [%s] for user[%s] " % (sdfgrillet,auser)
                    # print "do_detect_w_artt \n%s\n" % pprint.pformat(lartt)
            except:
                print_exc()
                print "method do_detect_w_artt 2" 
                return -1 
            
            nind += 1 
        return 1

        

# some method to patch database 
# they require a write access to the database 

    def do_set_dummy(self, dopt, odbcursor, odbcursordict, ddcid):
        ''' set database ready for inspection : password are dummyyed ,
authentication is forced to congesdatabase '''
        lsql_setdummy = [ 
            "UPDATE conges_users SET u_passwd = 'd5fbcc7c45e9b0183613e392f39e5d53' where u_login not in ('admin','conges') ;",
            "UPDATE conges_config SET conf_valeur = 'dbconges' where conf_nom = 'how_to_connect_user' ; " ,
            "COMMIT ;" 
            ] 
        # note d5fbcc7c45e9b0183613e392f39e5d53 result of echo md5($paw)."\n";  

        for asql in lsql_setdummy :
            try:
                # print "doing %s" % asql 
                odbcursor.execute(asql) 
            except:
                print_exc()
                print "err: do_set_dummy on %s" % asql 
                return -1
        print "do_set_dummy succeed."
        return 1 
