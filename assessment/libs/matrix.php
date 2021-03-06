<?php
//Matrix functions.  Version 1.5, Oct 8, 2014

global $allowedmacros;
array_push($allowedmacros,"matrix","matrixformat","matrixsystemdisp","matrixsum","matrixdiff","matrixscalar","matrixprod","matrixaugment","matrixrowscale","matrixrowswap","matrixrowcombine","matrixrowcombine3","matrixidentity","matrixtranspose","matrixrandinvertible","matrixrandunreduce","matrixinverse","matrixinversefrac","matrixsolve","matrixsolvefrac","polyregression","matrixgetentry","matrixgetrow","matrixgetcol","matrixgetsubmatrix","matrixdisplaytable","matrixreduce","matrixnumsolutions");

//matrix(vals,rows,cols)
//Creates a new matrix item.  
//Use matrixformat() to create display or $answer from a matrix item
//vals: list or array of numbers to form matrix values: R1C1,R1C2,...,R2C1,R2C2,...
//rows, cols: dimensions of matrix
//To define a matrix using calculations use:
//$m = matrix(array(3/2,2^3,5+1,3),2,2)
function matrix($vals,$rows,$cols) {
	$m = array();
	if (!is_array($vals)) {
		$vals = explode(',',$vals);
	}
	if (count($vals)!=$rows*$cols) {
		echo 'Number of matrix values does not match row/column count';
		return $m;
	}
	for ($i=0;$i<$rows;$i++) {
		$m[$i] = array();
	}
	for ($i=0;$i<count($vals);$i++) {
		$m[floor($i/$cols)][$i%$cols] = $vals[$i];
	}
	return $m;
}

//matrixformat(matrix)
//Formats a matrix item into an ASCIIMath string for display or $answer
function matrixformat($m) {
	$out = '[';
	for ($i=0; $i<count($m); $i++) {
		if ($i!=0) {
			$out .= ',';
		}
		$out .= '(';
		for ($j=0;$j<count($m[0]); $j++) {
			if ($j!=0) {
				$out .= ',';
			}
			$out.= $m[$i][$j];
		}
		$out .= ')';
	}
	$out .= ']';
	return $out;
}

//matrixdisplaytable(matrix, [matrixname, displayASCIIticks, linemode, header, tablestyle]) 
// Create a string that is a valid HTML table syntax for display.
// matrix: a matrix to be displayed in a HTML table
// matrixname: a string that holds the matrix name, like A or B.  This does not contain 
//   tick marks - if you want them you need to supply them.
//     default empty string
// displayASCIIticks: put tick marks around each element of the table, either 0 or 1.  
//   Use 0 if you are building an answerbox matrix.
//     0 do not use math ticks (default)
//     1 use math ticks
// linemode: Show none, augments, or simplex, value is either 0, 1 or 2
//    0 show no lines  (default)
//    1 show aumented line
//    2 show simplex  lines
// header: list or array of the variables "x1,x2,x3" that are used for the column titles.
//     default none
// tablestyle: for any additional styles for the table that you may want.  like "color:#40B3DF;"
//     default none
function matrixdisplaytable() {
  
  //  arguments lise --------------------------------------------------
  //  0 = matrix
  //  1 = matrix name
  //  2 = display ASCII tick marks (yes/no)
  //  3 = linemode - no line, aumented, or simplex
  //  4 = header column names, default is not to show
  //  5 = CSS tablestyle for the table.
  
  // process arguments -----------------------------------------------
  $args = func_get_args();
  
  if (count($args)==0) {
    echo "Nothing to display - no matrix supplied.<br/>";
    return "";
  }
  $m = $args[0];
  
  // matrixname
  if($args[1]!=null) { $matrixname = $args[1]; } else { $matrixname = ""; } 
  
  //displayASCII
	if($args[2]!=null) { 
    if($args[2]==0) { $ticks = ""; } else { $ticks = "`";}
  }
  else { $ticks = ""; }
  
  //mode
  if($args[3]!=null) {
    $mode = $args[3]; 
    if(($mode!=0)&&($mode!=1)&&($mode!=2)) { 
      echo "The supplied mode ($mode) is invalid.  Valid modes are 0,1,2.<br/>";
      $mode=1; 
    };
  } else { $mode=0; } 
  
  //header
  if($args[4]!=null) {
    $header = $args[4];
    if (!is_array($header)) { $header = explode(',',$header); }
  } else { $header = array(); }

  //tablestyle
  if($args[5]!=null) {
    $tablestyle = $args[5];
  } else {$tablestyle = ""; }
  
  // Done processing arguments ---------------------------------------
  //
  // style sheets
  
  $nopad = 'class="nopad"';
  $leftborder = "style='border-left:1px solid #999;padding-left:10px'";
  $preaug = 'style="padding-right:10px"';
  $preaugSimplex = 'style="padding-right:10px;border-top:1px solid #999;"';
  $Simplex = "style='border-top:1px solid #999;'";
  $leftborderSimplex = "style='border-left:1px solid #999;border-top:1px solid #999;'";
  
 // $mbtopleft = "style='border-left:1px solid black;border-top:1px solid black;padding:0;'";
 // $mbleft = "style='border-left:1px solid black;padding:0;'";
 // $mbbotleft = "style='border-left:1px solid black;border-bottom:1px solid black;padding:0;'";
 // $mbtopright = "style='border-right:1px solid black;border-top:1px solid black;padding:0;'";
 // $mbright = "style='border-right:1px solid black;padding:0;'";
 // $mbbotright = "style='border-right:1px solid black;border-bottom:1px solid black;padding:0;'";
  
  // counts
  $rows = count($m);
  $cols = count($m[0]);
  
  $lastrow = $rows-1;
  $lastcol = $cols-1;
    
  //$Tableau = "<table border='0' cellspacing='0' style='text-align:right;border-spacing: 0px 0px;$tablestyle'>\n";
  $Tableau = "<div style='display:inline-block;vertical-align:middle;'><table cellspacing='0' class='paddedtable' style='border-collapse:collapse;text-align:right;$tablestyle'>\n";
  $Tableau .= "<tbody>\n";
    
  for ($rloop=0; $rloop<$rows; $rloop++) {
	$Tableau .= "<tr>\n";
	if($rloop==0) { 
		if($matrixname!="") {
			if(count($header)>0) { $matricnamerows = $rows+1; } else { $matricnamerows = $rows; }
			$Tableau.= "<td rowspan='$matricnamerows'> $matrixname </td>\n";
		}
		if(count($header)>0)  {
			$Tableau.= "<td $nopad>&nbsp;</td>\n"; // for the left table border
			for ($cloop=0;$cloop<$cols; $cloop++) {
			  if(isset($header[$cloop])&&($header[$cloop]!=null)&&($header[$cloop]!="")) {
			    $Tableau.= "<td>".$ticks.$header[$cloop].$ticks."</td>";
			  } else {
			    $Tableau.= "<td>&nbsp;</td>";
			  }
			}
			$Tableau.= "<td $nopad>&nbsp;</td></tr>\n<tr>\n";  // for the right table border
		}
		
		//add left matrix bracket
		$Tableau.= "<td class=\"matrixtopleftborder\">&nbsp;</td>";
	} else if ($rloop==$lastrow) {
		$Tableau.= "<td class=\"matrixbottomleftborder\">&nbsp;</td>";
	} else {
		$Tableau.= "<td class=\"matrixleftborder\">&nbsp;</td>";
	}
	
	for ($cloop=0;$cloop<$cols; $cloop++) {
		$index =$rloop*$ctemp + $cloop;
		
		$TableElement = $ticks.$m[$rloop][$cloop].$ticks;
		
		if ($cloop==$lastcol && $mode>0) {  // R(last)C(Last)
			if ($mode==2 && $rloop==$lastrow){
				$Tableau.= "<td $leftborderSimplex>$TableElement</td>\n";
			} else {
				$Tableau.= "<td $leftborder>$TableElement</td>\n";
			} 
		}  else if ($cloop==$lastcol-1 && $mode>0) {
			if ($mode==2 && $rloop==$lastrow){ 
			    $Tableau.= "<td $preaugSimplex>$TableElement</td>\n";
			} else {
			    $Tableau.= "<td $preaug>$TableElement</td>\n";
			}
		} else {
			if($mode==2 && $rloop==$lastrow){ 
			    $Tableau.= "<td $Simplex>$TableElement</td>\n";
			} else {
			    $Tableau.= "<td>$TableElement</td>\n";
			}
		}
	}
	//add right matrix bracket
	if($rloop==0) {
		$Tableau.= "<td class=\"matrixtoprightborder\">&nbsp;</td>"; 
	} else if ($rloop==$lastrow) {
		$Tableau.= "<td class=\"matrixbottomrightborder\">&nbsp;</td>";
	} else {
		$Tableau.= "<td class=\"matrixrightborder\">&nbsp;</td>";
	}
	$Tableau.= "</tr>\n";
  }
    $Tableau.= "</tbody>\n";
    $Tableau.= "</table></div>\n";

    return $Tableau;
}

//matrixsystemdisp(matrix,[variables])
//Writes out a matrix as an equivalent system of equations
//variables is optional array of variables to use
function matrixsystemdisp($m,$v=array('x','y','z','w','v')) {
	$out = '{';
	for ($i=0; $i<count($m); $i++) {
		if ($i!=0) {
			$out .= ',';
		}
		$out .= '(';
		$firstout = false;
		for ($j=0; $j<count($m[0]); $j++) {
			if ($j!=0) {
				$out .= ',';
			}
			if ($j==count($m[0])-1) {
				$out .= '=,'.$m[$i][$j];
				break;
			}
			if (!is_numeric($m[$i][$j]) && $firstout) {  //something like a variable coefficient
				$out .= '+,';
			} else if ($m[$i][$j]==0) {
				$out .= ",";
			} else if ($m[$i][$j]<0) {
				$out .= "-,";
			} else if ($firstout) {
				$out .= "+,";
			} else {
				$out .= ',';
			}
			if (!is_numeric($m[$i][$j])) {
				$out .= $m[$i][$j];
			} else if ($m[$i][$j]!=0 && abs($m[$i][$j])!=1) {
				$out .= abs($m[$i][$j]);
			}
			if ((!is_numeric($m[$i][$j]) || $m[$i][$j]!=0) && $j<count($m[0])-1) {
				$firstout = true;
				$out .= $v[$j];
			}
			
		}
		$out .= ')';
	}
	$out .= ':}';
	return $out;
}

//matrixgetentry(matrix,row,col)
//get entry from a matrix at given row and col
//rows and cols are 0 indexed (first row is row 0)
function matrixgetentry($m,$r,$c) {
	if ($r<0 || $c<0 || $r>=count($m) || $c>=count($m[0])) {
		echo 'invalid row or column';
		return 0;
	} else {
		return $m[$r][$c];
	}
}

//matrixgetrow(matrix,row)
//get row of a matrix as a new 1xm matrix
//rows and cols are 0 indexed (first row is row 0)
function matrixgetrow($m,$r) {
	if ($r<0 || $r>=count($m)) {
		echo 'invalid row';
	} else {
		return array($m[$r]);
	}
}

//matrixgetcol(matrix,col)
//get col of a matrix as a new nx1 matrix
//rows and cols are 0 indexed (first row is row 0)
function matrixgetcol($m,$c) {
	if ($c<0 || $c>=count($m[0])) {
		echo 'invalid col';
	} else {
		$o = array();
		foreach ($m as $r=>$row) {
			$o[$r] = array($row[$c]);
		}
		return $o;
	}
}

//matrixgetsubmatrix(matrix,rowselector,colselector)
//gets submatrix.  rowselector and colselector are strings
//with format:  "start:end".  ":" to select all 
function matrixgetsubmatrix($m,$rs,$cs) {
	$rsp = explode(':',$rs);
	if (count($rsp)<2) {
		$rstart = 0;  $rend = count($m)-1;
	} else {
		if ($rsp[0]!='') {
			$rstart = intval($rsp[0]);
		} else {
			$rstart = 0;
		}
		if ($rsp[1]!='') {
			$rend = intval($rsp[1]);
		} else {
			$rend = count($m)-1;
		}
	}
	$csp = explode(':',$cs);
	if (count($csp)<2) {
		$cstart = 0;  $cend = count($m[0])-1;
	} else {
		if ($csp[0]!='') {
			$cstart = intval($csp[0]);
		} else {
			$cstart = 0;
		}
		if ($csp[1]!='') {
			$cend = intval($csp[1]);
		} else {
			$cend = count($m[0])-1;
		}
	}
	$o = array();
	for ($i=$rstart; $i<=$rend; $i++) {
		$o[$i-$rstart] = array();
		for ($j=$cstart; $j<=$cend; $j++) {
			$o[$i-$rstart][$j-$cstart] = $m[$i][$j];
		}
	}
	return $o;
}

//matrixsum(matrix,matrix)
//Adds two matrices
function matrixsum($m,$n) {
	if (count($m)!=count($n) || count($m[0])!=count($n[0])) {
		echo 'matrix size does not match: cannot add';
		return $m;
	}
	for ($i=0; $i<count($m); $i++) {
		for ($j=0; $j<count($m[0]); $j++) {
			$m[$i][$j] += $n[$i][$j];
		}
	}
	return $m;
}

//matrixdiff(matrix1,matrix2)
//Subtracts matrix1-matrix2
function matrixdiff($m,$n) {
	if (count($m)!=count($n) || count($m[0])!=count($n[0])) {
		echo 'matrix size does not match: cannot add';
		return $m;
	}
	for ($i=0; $i<count($m); $i++) {
		for ($j=0; $j<count($m[0]); $j++) {
			$m[$i][$j] = $m[$i][$j] - $n[$i][$j];
		}
	}
	return $m;
}			

//matrixscalar(matrix,n)
//Multiplies the matrix times the number n
function matrixscalar($m,$n) {
	for ($i=0; $i<count($m); $i++) {
		for ($j=0; $j<count($m[0]); $j++) {
			$m[$i][$j] *= $n;
		}
	}
	return $m;
}	

//matrixprod(matrix1,matrix2)
//Calculates the matrix product matrix1*matrix2
function matrixprod($m,$n) {
	if (count($m[0])!=count($n)) {
		echo 'matrix sizes do not allow product';
		return $m;
	}
	if (count($m)*count($m[0])*count($n[0])>1000) {
		global $myrights;
		if ($myrights>10) {
			echo "matrixprod: You really shouldn't do products of very large matrices. ";
		}
	}
	$o = array();
	$o = array();
	for ($i=0;$i<count($m); $i++) {
		for ($j=0;$j<count($n[0]); $j++) {
			$v = 0;
			for ($k=0; $k<count($m[0]); $k++) {
				$v += $m[$i][$k]*$n[$k][$j];
			}
			$o[$i][$j] = $v;
		}
	}
	return $o;
}

//matrixaugment(matrix1,matrix2)
//Augments matrix2 to the right side of matrix1
function matrixaugment($m,$n) {
	if (count($m)!=count($n)) {
		echo 'row count does not match: cannot augment';
		return $m;
	}
	for ($i=0; $i<count($m); $i++) {
		$m[$i] = array_merge($m[$i],$n[$i]);
	}
	return $m;
}

//matrixrowscale(matrix,row,n)
//Multiplies row of matrix by n
//matrix rows are 0-indexed; first row is row 0
function matrixrowscale($m,$r,$n) {
	for ($j=0; $j<count($m[$r]); $j++) {
		$m[$r][$j] *= $n;
	}
	return $m;
}
	
//matrixrowswap(matrix,row1,row2)
//swaps rows in matrix
//matrix rows are 0-indexed; first row is row 0
function matrixrowswap($m,$r,$t) {
	$temp = $m[$t];
	$m[$t] = $m[$r];
	$m[$r] = $temp;
	return $m;	
}

//matrixrowcombine(matrix,row1,a,row2,b,endrow)
//replaces endrow in matrix with a*row1 + b*row2
//matrix rows are 0-indexed; first row is row 0
function matrixrowcombine($m,$r1,$a,$r2,$b,$s) {
	for ($j=0; $j<count($m[$s]); $j++) {
		$m[$s][$j] = $a*$m[$r1][$j] + $b*$m[$r2][$j];
	}
	return $m;
}

//matrixrowcombine3(matrix,row1,a,row2,b,row3,c,endrow)
//replaces endrow in matrix with a*row1 + b*row2 + c*row3
//matrix rows are 0-indexed; first row is row 0
function matrixrowcombine3($m,$r1,$a,$r2,$b,$r3,$c,$s) {
	for ($j=0; $j<count($m[$s]); $j++) {
		$m[$s][$j] = $a*$m[$r1][$j] + $b*$m[$r2][$j] + $c*$m[$r3][$j];
	}
	return $m;
}

//matrixidentity(n)
//Creates an n x n identity matrix
function matrixidentity($n) {
	$m = array();
	for ($i=0; $i< $n; $i++) {
		$m[$i] = array();
		for ($j=0; $j<$n; $j++) {
			$m[$i][$j] = 0;
		}
		$m[$i][$i] = 1;
	}
	return $m;
}

//matrixtranspose(m)
//Calculates the transpose of the matrix m
function matrixtranspose($m) {
	$n = array();
	for ($c=0; $c<count($m[0]); $c++) {
		$n[$c] = array();
		for ($r=0; $r<count($m); $r++) {
			$n[$c][$r] = $m[$r][$c];
		}
	}
	return $n;	
}

//randinvertible(n)
//Creates a random n x n invertible matrix by applying random row combinations to an identity matrix
//returns an array of two matrices:  M and M^-1
function matrixrandinvertible($n) {
	$m = matrixidentity($n);
	$mi = matrixidentity($n);
	$ops = array();
	$mult = nonzerodiffrands(-3,3,5);
	for ($i=0; $i<5; $i++) {
		list($sr,$er) = diffrands(0,$n-1,2);
		$ops[$i] = array($sr,$er);
		$m = matrixrowcombine($m,$sr,$mult[$i],$er,1,$er);
	}
	for ($i=4; $i>-1; $i--) {
		$mi = matrixrowcombine($mi,$ops[$i][0],-1*$mult[$i],$ops[$i][1],1,$ops[$i][1]);	
	}
	return array($m,$mi);
}

//matrixrandunreduce(m,n)
//Randomizes the matrix m by applying n random row combinations
function matrixrandunreduce($m,$c) {
	$n = count($m);
	for ($i=0;$i<$c; $i++) {
		$r = diffrands(0,$n-1,3);
		$m = matrixrowcombine3($m,$r[0],-1,$r[1],1,$r[2],2,$r[0]);
	}
	for ($i=0; $i<$c; $i++) {
		list($sr,$er) = diffrands(0,$n-1,2);
		$m = matrixrowswap($m,$sr,$er);
	}
	$c = 0;
	while (hasallzerorow($m) && $c<20) {
		$r = diffrands(0,$n-1,3);
		$m = matrixrowcombine3($m,$r[0],-2,$r[1],1,$r[2],3,$r[0]);
		$c++;
	}
	return $m;
}

function hasallzerorow($m) {
	$n = count($m);
	$nc = count($m[0]);
	for ($i=0;$i<$n;$i++) {
		for ($j=0;$j<$nc;$j++) {
			if ($m[$i][$j]!=0) {
				continue 2;
			}
		}
		return true;
	}
	return false;
}
//matrixinverse(m)
//Finds the inverse of nxn matrices.
function matrixinverse($m) {
	if (count($m[0])!=count($m)) {
		echo 'matrix must be square';
		return $m;
	}
	return matrixsolve($m,matrixidentity(count($m)));
}

//matrixinversefrac(m)
//Finds the inverse of nxn matrices, with the resulting entries as fractions
//the fraction entries are strings, so do NOT try to use the result of this
//for calculations.
function matrixinversefrac($m) {
	if (count($m[0])!=count($m)) {
		echo 'matrix must be square';
		return $m;
	}
	return matrixsolvefrac($m,matrixidentity(count($m)));
}

//matrixsolve(A,b)
//solves the matrix equation Ax = b
//A and b are both matrices
//A is nxn, b is nxm
//returns nxm matrix x so Ax = b
function matrixsolve($A, $b, $silenterror=false) {    
	if (count($A) != count($A[0])) {
		echo "can only solve for square matrices A, sorry"; return $b;
	}
	if (count($b)!=count($A)) {
		echo "A and b must have same number of rows"; return $b;
	}
    // number of rows
    $N  = count($b);
    $M = count($b[0]); //number of cols in $b
    if ($N>10) {
	global $myrights;
	if ($myrights>10) {
		echo "You really shouldn't use matrixsolve for matrices bigger than 10 rows.";
	}
    }
    // forward elimination
    for ($p=0; $p<$N; $p++) {

      // find pivot row and swap
      $max = $p;
      for ($i = $p+1; $i < $N; $i++)
        if (abs($A[$i][$p]) > abs($A[$max][$p]))
          $max = $i;
      $temp = $A[$p]; $A[$p] = $A[$max]; $A[$max] = $temp;
      $t    = $b[$p]; $b[$p] = $b[$max]; $b[$max] = $t;
     
      // check if matrix is singular
      if (abs($A[$p][$p]) <= 1e-10) {
      	      if ($silenterror) {
      	      	      return false;
      	      } else {
      	      	      echo("Solve failed: Matrix is singular or nearly singular"); return $b;
      	      }
      }

      // pivot within A and b
      for ($i = $p+1; $i < $N; $i++) {
        $alpha = $A[$i][$p] / $A[$p][$p];
	for ($j=0;$j<$M;$j++) {
		$b[$i][$j] -= $alpha * $b[$p][$j];
	}
        for ($j = $p; $j < $N; $j++)
          $A[$i][$j] -= $alpha * $A[$p][$j];
      }
    }

    // zero the solution vector
    $x = array();
    for ($c=0;$c<$M;$c++) {
	    $x[$c] = array_fill(0, $N-1, 0);
	
	    // back substitution
	    for ($i = $N - 1; $i >= 0; $i--) {
	      $sum = 0.0;
	      for ($j = $i + 1; $j < $N; $j++)
		$sum += $A[$i][$j] * $x[$c][$j];
	      $x[$c][$i] = ($b[$i][$c] - $sum) / $A[$i][$i];
	    }
    }
    return matrixtranspose($x);

}

//matrixsolvefrac(A,b)
//solves the matrix equation Ax = b
//A and b are both matrices
//A is nxn, b is nxm
//returns nxm matrix x so Ax = b
//entries may be fractions (as strings), so don't 
//try to use the result in calculations. 
function matrixsolvefrac($A, $b, $asString=true) {    
	if (count($A) != count($A[0])) {
		echo "can only solve for square matrices A, sorry"; return $b;
	}
	if (count($b)!=count($A)) {
		echo "A and b must have same number of rows"; return $b;
	}
	include_once("fractions.php");
    // number of rows
    $N  = count($b);
    $M = count($b[0]); //number of cols in $b
    if ($N>10) {
	global $myrights;
	if ($myrights>10) {
		echo "You really shouldn't use matrixsolvefrac for matrices bigger than 10 rows.";
	}
    }
    for ($r=0;$r<$N;$r++) {
    	    for ($c=0;$c<$N;$c++) {
    	    	    $A[$r][$c] = fractionparse($A[$r][$c]);
    	    }
    	    for ($c=0;$c<$M;$c++) {
    	    	    $b[$r][$c] = fractionparse($b[$r][$c]);
    	    }
    }
    
    // forward elimination
    for ($p=0; $p<$N; $p++) {

      // find pivot row and swap
      $max = $p;
      for ($i = $p+1; $i < $N; $i++)
        if (abs($A[$i][$p][0]/$A[$i][$p][1]) > abs($A[$max][$p][0]/$A[$max][$p][1]))
          $max = $i;
  
      $temp = $A[$p]; $A[$p] = $A[$max]; $A[$max] = $temp;
      $t    = $b[$p]; $b[$p] = $b[$max]; $b[$max] = $t;
     
      // check if matrix is singular
      if (abs($A[$p][$p][0]/$A[$p][$p][1]) <= 1e-10) {echo("Solve failed: Matrix is singular or nearly singular"); return $b;}

      // pivot within A and b
      for ($i = $p+1; $i < $N; $i++) {
        $alpha = fractiondivide($A[$i][$p], $A[$p][$p]);
	for ($j=0;$j<$M;$j++) {
		//$b[$i][$j] -= $alpha * $b[$p][$j];
		$b[$i][$j] = fractionsubtract($b[$i][$j], fractionmultiply($alpha, $b[$p][$j]));
	}
        for ($j = $p; $j < $N; $j++)
          //$A[$i][$j] -= $alpha * $A[$p][$j];
  	  $A[$i][$j] = fractionsubtract($A[$i][$j], fractionmultiply($alpha, $A[$p][$j]));
      }
    }

    // zero the solution vector
    $x = array();
    for ($c=0;$c<$M;$c++) {
	    $x[$c] = array_fill(0, $N-1, array(0,1));
	
	    // back substitution
	    for ($i = $N - 1; $i >= 0; $i--) {
	      $sum = array(0,1);
	      for ($j = $i + 1; $j < $N; $j++) {
		//$sum += $A[$i][$j] * $x[$c][$j];
		$sum = fractionadd($sum, fractionmultiply($A[$i][$j], $x[$c][$j]));
	      }
	      //$x[$c][$i] = ($b[$i][$c] - $sum) / $A[$i][$i];
	      $x[$c][$i] = fractiondivide(fractionsubtract($b[$i][$c], $sum), $A[$i][$i]);
	    }
    }
    for ($c=0;$c<$M;$c++) {
    	    for ($r=0;$r<$N;$r++) {
    	    	    $x[$c][$r] = fractionreduce($x[$c][$r]);
    	    }
    }
    return matrixtranspose($x);
}

//matrixreduce(A,[rref,frac])
//reduces the matrix A to echelon or reduced echelon form
//A is a matrix
//rref = true for rref, false for echelon (default)
//frac = true for fraction output, false for decimal output (default)
//  if true, entries may be fractions (as strings), so don't 
//  try to use the result in calculations. 
//NOTE:  In most cases, using matrixrandunreduce is a better option than using this!
function matrixreduce($A, $rref = false, $frac = false) {    
	include_once("fractions.php");
    // number of rows
    $N  = count($A);
    $M = count($A[0]);
    $pivots = array();
    if ($N>10) {
	global $myrights;
	if ($myrights>10) {
		echo "You really shouldn't use matrixreduce for matrices bigger than 10 rows.";
	}
    }
    for ($r=0;$r<$N;$r++) {
    	    for ($c=0;$c<$M;$c++) {
    	    	    $A[$r][$c] = fractionparse($A[$r][$c]);
    	    }
    }
   
    $r = 0;  $c = 0;
    
    while ($r < $N && $c < $M) {
    	    if ($A[$r][$c][0]==0) { //swap only if there's a 0 entry
		    $max = $p;
		    for ($i = $r+1; $i < $N; $i++) {
			    if (abs($A[$i][$c][0]/$A[$i][$c][1]) > abs($A[$max][$c][0]/$A[$max][$c][1])) {
				$max = $i;
			    }
		    }
		    if ($max != $p) { 
			$temp = $A[$r]; $A[$r] = $A[$max]; $A[$max] = $temp;
		    }
    	    }
    	    
    	    if (abs($A[$r][$c][0]/$A[$r][$c][1]) <= 1e-10) {
    	    	    $c++;
    	    	    continue;
    	    }
    	    
    	    //scale pivot row
    	    if ($rref) {
		    $divisor = $A[$r][$c];
		    for ($j = $c; $j < $M; $j++) {
			    $A[$r][$j] = fractiondivide($A[$r][$j],$divisor);
		    }
    	    }
 
    	    for ($i = ($rref?0:$r+1); $i < $N; $i++) {
    	    	    if ($i==$r) {continue;}
    	    	    $mult = fractiondivide($A[$i][$c],$A[$r][$c]);
    	    	    if ($mult[0]==0) {continue;}
    	    	    for ($j = $c; $j < $M; $j++) {
    	    	    	    //echo "Entry $i,$j:  ".fractionreduce($A[$i][$j]).' - '.fractionreduce( $mult).'*'.fractionreduce($A[$r][$j]).'<br/>';
    	    	    	    $A[$i][$j] = fractionsubtract($A[$i][$j], fractionmultiply($mult,$A[$r][$j]));
    	    	    }
    	    }
    	 
    	    $r++; $c++;
    }
    
    for ($r=0;$r<$N;$r++) {
    	    for ($c=0;$c<$M;$c++) {
    	    	    if ($frac) {
    	    	    	    $A[$r][$c] = fractionreduce($A[$r][$c]);
    	    	    } else {
    	    	    	    $A[$r][$c] = $A[$r][$c][0]/$A[$r][$c][1];
    	    	    }
    	    }
    }
    return $A;
}

//matrixnumsolutions(A,n)
//A is an arbitrary coefficient matrix augmented with n columns, after
// being row reduced to reduced echelon form (see matrixreduce)
//Returns the number of Ax=b equations that have at least one solution
function matrixnumsolutions($A,$n=0) {
	$c = count($A[0]);
	$Ac = $c - $n;
	$r = count($A);
	$nosolution = array();
	for ($i=0; $i<$r; $i++) {
		for ($j=0; $j<$Ac; $j++) {
			if (abs($A[$i][$j])>1e-10) {
				continue 2;
			}
		}
		//is all zeros on left
		for ($j=$Ac;$j<$c;$j++) {
			if (abs($A[$i][$j])>1e-10) {
				$nosolution[$j] = 1;
			}
		}
	}
	return ($n - count($nosolution));
}


//polyregression(x,y,n)
//find a nth degree polynomial that best fits the data 
//x,y arrays of data
//returns an array (intercept, linear coeff, quad coeff, ...)
function polyregression($x,$y,$n) {
	$m = array();
	for ($i=0;$i<count($x);$i++) {
		$m[$i][0] = 1;
		for ($j=1;$j<=$n;$j++) {
			$m[$i][$j] = $m[$i][$j-1]*$x[$i];
		}
	}
	$m = matrixsolve(matrixprod(matrixtranspose($m),$m),matrixprod(matrixtranspose($m),matrix($y,count($y),1)));
	$m = matrixtranspose($m);
	return $m[0];
}



?>
