extends PlayerState

func EnterState():
	Name = "Run"


func ExitState():
	pass


func Update(delta: float):
	Player.HorizontalMovement()
	Player.HandleJump()
	Player.HandleFalling()
	
	HandleAnimations()
	HandleIdle()

func HandleIdle():
	if (Player.moveDirectionX == 0):
		Player.ChangeState(States.Idle)

func HandleAnimations():
	Player.animator.play("Jog")
	Player.HandleFlipH
